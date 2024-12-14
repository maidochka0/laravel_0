<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\All;

class ApiDataController extends Controller
{

    public function fetchSales(Request $request)
    {
        // Получаем параметры из запроса
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $limit = $request->input('limit');
        $page = $request->input('page');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;

        $response = Http::get("http://89.108.115.241:6969/api/sales", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => All::getPrivateKey(),
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $data = $response->json()['data'];

            foreach ($data as $saleData) {
                Sale::create([
                    'g_number' => $saleData['g_number'],
                    'date' => $saleData['date'],
                    'last_change_date' => $saleData['last_change_date'],
                    'supplier_article' => $saleData['supplier_article'],
                    'tech_size' => $saleData['tech_size'],
                    'barcode' => $saleData['barcode'],
                    'total_price' => $saleData['total_price'],
                    'discount_percent' => $saleData['discount_percent'],
                    'is_supply' => $saleData['is_supply'],
                    'is_realization' => $saleData['is_realization'],
                    'promo_code_discount' => $saleData['promo_code_discount'],
                    'warehouse_name' => $saleData['warehouse_name'],
                    'country_name' => $saleData['country_name'],
                    'oblast_okrug_name' => $saleData['oblast_okrug_name'],
                    'region_name' => $saleData['region_name'],
                    'income_id' => $saleData['income_id'],
                    'sale_id' => $saleData['sale_id'],
                    'odid' => $saleData['odid'],
                    'spp' => $saleData['spp'],
                    'for_pay' => $saleData['for_pay'],
                    'finished_price' => $saleData['finished_price'],
                    'price_with_disc' => $saleData['price_with_disc'],
                    'nm_id' => $saleData['nm_id'],
                    'subject' => $saleData['subject'],
                    'category' => $saleData['category'],
                    'brand' => $saleData['brand'],
                    'is_storno' => $saleData['is_storno'],
                ]);
            }

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc'], 500);
    }

    public function fetchIncomes(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;

        $response = Http::get("http://89.108.115.241:6969/api/incomes", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => All::getPrivateKey(),
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'incomes';
            $data = $response->json()['data'];

            $this->add($table, $data);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc'], 500);
    }

    public function fetchStocks(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$dateFrom) $dateFrom = date('Y-m-d');

        $response = Http::get("http://89.108.115.241:6969/api/stocks", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => All::getPrivateKey(),
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'stocks';
            $data = $response->json()['data'];

            $this->add($table, $data);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc. try with default par'], 500);
    }

    public function fetchOrders(Request $request)
    {
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;

        $response = Http::get("http://89.108.115.241:6969/api/orders", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => All::getPrivateKey(),
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'orders';
            $data = $response->json()['data'];

            $this->add($table, $data);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc'], 500);
    }

    private function add($table, $data)
    {
        DB::table($table)->insert($data);
    }
}