@extends('v_layouts.app')

@section('content')
<div class="col-md-12">
    <div class="billing-details">
        <div class="section-title">
            <p>PENGIRIMAN</p>
            <h3 class="title">Cek Ongkos Kirim</h3>
        </div>

        <div class="alert alert-info">
            Pilih provinsi, kota, dan kecamatan tujuan. Berat paket menggunakan satuan gram.
        </div>
        <div id="ongkir-error" class="alert alert-danger" style="display:none"></div>
        <div id="ongkir-loading" class="alert alert-info" style="display:none">Mengambil data dari RajaOngkir...</div>

        <form id="ongkirForm">
            @csrf
            <div class="form-group">
                <label for="province">Provinsi Tujuan</label>
                <select class="form-control" id="province" required>
                    <option value="">Memuat provinsi...</option>
                </select>
            </div>
            <div class="form-group">
                <label for="city">Kota/Kabupaten Tujuan</label>
                <select class="form-control" id="city" required disabled>
                    <option value="">Pilih provinsi terlebih dahulu</option>
                </select>
            </div>
            <div class="form-group">
                <label for="district">Kecamatan Tujuan</label>
                <select class="form-control" id="district" required disabled>
                    <option value="">Pilih kota terlebih dahulu</option>
                </select>
            </div>
            <div class="form-group">
                <label for="weight">Berat Paket (gram)</label>
                <input class="form-control" type="number" id="weight" min="1" value="1000" required>
            </div>
            <div class="form-group">
                <label for="courier">Jasa Pengiriman</label>
                <select class="form-control" id="courier" required>
                    <option value="">Pilih Kurir</option>
                    <option value="jne">JNE</option>
                    <option value="tiki">TIKI</option>
                    <option value="pos">POS Indonesia</option>
                </select>
            </div>
            <button type="submit" class="primary-btn">Cek Ongkir</button>
        </form>

        <div id="result" style="margin-top:20px"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const result = document.getElementById('result');
    const errorBox = document.getElementById('ongkir-error');
    const loading = document.getElementById('ongkir-loading');
    const originDistrictId = Number(@json($originDistrictId || 0));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    };
    const clearError = () => {
        errorBox.textContent = '';
        errorBox.style.display = 'none';
    };
    const readData = (payload) => {
        if (!payload || !Array.isArray(payload.data)) {
            throw new Error(payload?.message || payload?.meta?.message || 'Data dari RajaOngkir tidak tersedia.');
        }
        return payload.data;
    };

    fetch('{{ route('ongkir.provinces') }}', {headers: {'Accept': 'application/json'}})
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) => {
            if (!ok) throw new Error(data?.message || data?.meta?.message || 'Tidak dapat mengambil data provinsi.');
            const provinces = readData(data);
            provinceSelect.innerHTML = '<option value="">Pilih Provinsi</option>';
            provinces.forEach(province => {
                const option = document.createElement('option');
                option.value = province.id;
                option.textContent = province.name;
                provinceSelect.appendChild(option);
            });
        })
        .catch(error => showError(error.message));

    provinceSelect.addEventListener('change', () => {
        clearError();
        result.innerHTML = '';
        citySelect.disabled = true;
        districtSelect.disabled = true;
        citySelect.innerHTML = '<option value="">Memuat kota...</option>';
        districtSelect.innerHTML = '<option value="">Pilih kota terlebih dahulu</option>';
        if (!provinceSelect.value) return;

        fetch(`{{ url('/cities') }}?province_id=${encodeURIComponent(provinceSelect.value)}`, {headers: {'Accept': 'application/json'}})
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok) throw new Error(data?.message || data?.meta?.message || 'Tidak dapat mengambil data kota.');
                const cities = readData(data);
                citySelect.innerHTML = '<option value="">Pilih Kota/Kabupaten</option>';
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
                citySelect.disabled = false;
            })
            .catch(error => showError(error.message));
    });

    citySelect.addEventListener('change', () => {
        clearError();
        result.innerHTML = '';
        districtSelect.disabled = true;
        districtSelect.innerHTML = '<option value="">Memuat kecamatan...</option>';
        if (!citySelect.value) return;

        fetch(`{{ url('/districts') }}?city_id=${encodeURIComponent(citySelect.value)}`, {headers: {'Accept': 'application/json'}})
            .then(response => response.json().then(data => ({ok: response.ok, data})))
            .then(({ok, data}) => {
                if (!ok) throw new Error(data?.message || data?.meta?.message || 'Tidak dapat mengambil data kecamatan.');
                const districts = readData(data);
                districtSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district.id;
                    option.textContent = district.name;
                    districtSelect.appendChild(option);
                });
                districtSelect.disabled = false;
            })
            .catch(error => showError(error.message));
    });

    document.getElementById('ongkirForm').addEventListener('submit', event => {
        event.preventDefault();
        clearError();
        result.innerHTML = '';

        if (!originDistrictId) {
            showError('Isi RAJAONGKIR_ORIGIN_DISTRICT_ID pada file .env dengan ID kecamatan asal toko.');
            return;
        }

        loading.style.display = 'block';
        fetch('{{ route('ongkir.cost') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                destination: districtSelect.value,
                weight: document.getElementById('weight').value,
                courier: document.getElementById('courier').value
            })
        })
        .then(response => response.json().then(data => ({ok: response.ok, data})))
        .then(({ok, data}) => {
            if (!ok) throw new Error(data?.message || data?.meta?.message || 'Permintaan ongkos kirim gagal.');
            const costs = readData(data);
            if (costs.length === 0) throw new Error('Tidak ada layanan pengiriman untuk tujuan tersebut.');

            const heading = document.createElement('h4');
            heading.textContent = 'Hasil Ongkos Kirim';
            result.appendChild(heading);
            const list = document.createElement('div');
            list.className = 'list-group';
            costs.forEach(item => {
                const row = document.createElement('div');
                row.className = 'list-group-item';
                row.textContent = `${item.name || item.code} - ${item.service || ''}: Rp ${Number(item.cost || 0).toLocaleString('id-ID')} (Estimasi ${item.etd || '-'} hari)`;
                list.appendChild(row);
            });
            result.appendChild(list);
        })
        .catch(error => showError(error.message))
        .finally(() => loading.style.display = 'none');
    });
});
</script>
@endsection
