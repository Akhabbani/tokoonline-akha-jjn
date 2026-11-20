<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    private function client()
    {
        $key = config('services.rajaongkir.key');
        $baseUrl = rtrim((string) config('services.rajaongkir.base_url'), '/');

        if (!$key || !$baseUrl) {
            return null;
        }

        return Http::timeout(25)->acceptJson()->withHeaders(['key' => $key]);
    }

    public function getProvinces()
    {
        $client = $this->client();
        if (!$client) {
            return response()->json(['message' => 'API Key RajaOngkir belum diisi pada file .env.'], 503);
        }

        $url = rtrim(config('services.rajaongkir.base_url'), '/') . '/destination/province';
        $response = $client->get($url);

        return response()->json($response->json(), $response->status());
    }

    public function getCities(Request $request)
    {
        $validated = $request->validate(['province_id' => 'required|integer|min:1']);
        $client = $this->client();
        if (!$client) {
            return response()->json(['message' => 'API Key RajaOngkir belum diisi pada file .env.'], 503);
        }

        $baseUrl = rtrim(config('services.rajaongkir.base_url'), '/');
        $response = $client->get($baseUrl . '/destination/city/' . $validated['province_id']);

        return response()->json($response->json(), $response->status());
    }

    public function getDistricts(Request $request)
    {
        $validated = $request->validate(['city_id' => 'required|integer|min:1']);
        $client = $this->client();
        if (!$client) {
            return response()->json(['message' => 'API Key RajaOngkir belum diisi pada file .env.'], 503);
        }

        $baseUrl = rtrim(config('services.rajaongkir.base_url'), '/');
        $response = $client->get($baseUrl . '/destination/district/' . $validated['city_id']);

        return response()->json($response->json(), $response->status());
    }

    public function getCost(Request $request)
    {
        $validated = $request->validate([
            'destination' => 'required|integer|min:1',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|in:jne,tiki,pos',
        ]);

        $originDistrictId = (int) config('services.rajaongkir.origin_district_id');
        if ($originDistrictId < 1) {
            return response()->json([
                'message' => 'Isi RAJAONGKIR_ORIGIN_DISTRICT_ID dengan ID kecamatan asal toko pada file .env.',
            ], 422);
        }

        $client = $this->client();
        if (!$client) {
            return response()->json(['message' => 'API Key RajaOngkir belum diisi pada file .env.'], 503);
        }

        $payload = [
            'origin' => $originDistrictId,
            'destination' => $validated['destination'],
            'weight' => $validated['weight'],
            'courier' => $validated['courier'],
            'price' => 'lowest',
        ];

        $baseUrl = rtrim(config('services.rajaongkir.base_url'), '/');
        $response = $client->asForm()->post($baseUrl . '/calculate/district/domestic-cost', $payload);

        return response()->json($response->json(), $response->status());
    }

    public function index()
    {
        return view('v_order.ongkir', [
            'originDistrictId' => config('services.rajaongkir.origin_district_id'),
        ]);
    }
}
