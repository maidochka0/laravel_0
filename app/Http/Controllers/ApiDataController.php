<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\Sale;
use App\Models\All;
use App\Models\Token;
use App\Models\Account;
use Carbon\Carbon;


define('DEBUG_MOD', 1);
define('CRON_ACCOUNT_ID', null); //не задается, т.к. я его не создавал в бд
//define requests_limit

class ApiDataController extends Controller
{

    public function fetchSales(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $limit = $request->input('limit');
        $page = $request->input('page');
        $key = $request->input('key');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$key) $key = All::getPrivateKey();

        $isLastPage = ($limit === 500) ? false : true;
        while ($isLastPage === false) {
            $response = Http::get("http://89.108.115.241:6969/api/sales", [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'page' => $page,
                'key' => $key,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'];
                $accountId = $this->getAccountIdByToken($key);

                $salesData = [];
                foreach ($data as $saleData) {
                    $salesData[] = [
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
                        'account_id' => $accountId
                    ];
                }
                Sale::insert($salesData);
            } else return response()->json(['message' => 'unsucc'], 500);

            if (++$page === $response->json()['meta']['last_page']) $isLastPage = true;
        }
        return response()->json(['message' => 'succ']);

        
    }

    public function fetchIncomes(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;
        
        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');
        $key = $request->input('key');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$key) $key = All::getPrivateKey();

        $response = Http::get("http://89.108.115.241:6969/api/incomes", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'incomes';
            $data = $response->json()['data'];

            $this->add($table, $data, $key);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc'], 500);
    }

    public function fetchStocks(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');
        $key = $request->input('key');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$dateFrom) $dateFrom = date('Y-m-d');
        if (!$key) $key = All::getPrivateKey();

        $response = Http::get("http://89.108.115.241:6969/api/stocks", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'stocks';
            $data = $response->json()['data'];

            $this->add($table, $data, $key);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc. try with default par'], 500);
    }

    public function fetchOrders(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');
        $key = $request->input('key');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$key) $key = All::getPrivateKey();

        $response = Http::get("http://89.108.115.241:6969/api/orders", [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => $page,
            'key' => $key,
            'limit' => $limit,
        ]);

        if ($response->successful()) {
            $table = 'orders';
            $data = $response->json()['data'];

            $this->add($table, $data, $key);

            return response()->json(['message' => 'succ']);
        }

        return response()->json(['message' => 'unsucc'], 500);
    }

    private function add($table, $data, $token)
    {
        $accountId = $this->getAccountIdByToken($token);
        $this->addAccountIdInData($data, $accountId);

        DB::table($table)->insert($data);
    }

    private function addAccountIdInData(&$data, $id)
    {
        $data = array_map(function($item) use($id) {
            $item['account_id'] = $id;
            return $item;
        }, $data);
    }

    public static function dailyUpdate()
    {
        //эта функция не валидируется на количество запросов, т.к. запускается только из заданого крон. и вообще она в целом держиться как обособленная функция чисто под 1 задачу для крон, как-будто ей не место в контролере. пришлось прибегнуть к такому костыльному решению, т.к. не смог нормально настроить ежедневный вызов через встроенные инструменты ларавель.
        
        $dateFrom = date('Y-m-d');
        $dateTo = date('Y-m-d');
        $page = 1;
        $limit = 500;
        
        foreach(ALL::getTables() as $table) {
            $response = Http::get("http://89.108.115.241:6969/api/$table", [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'page' => $page,
                'key' => All::getPrivateKey(),
                'limit' => $limit,
            ]);

            if (!$response->successful()) return "$table-err";

            $data = $response->json()['data'];
            $data = array_map(function($item) {
                $item['account_id'] = CRON_ACCOUNT_ID;
                return $item;
            }, $data);

            DB::table($table)->insertOrIgnore($data); //можно добавить проверку на дубликаты из бд. хотя все было бы проще, если бы апи возвращал индексированные записи
        }

        //print in cron.log
        print "daily update succ\n";
        print "-\n";
    }

    private function handleRateLimiting(Request $request)
    {
        $this->debugMess("request from ip ".$request->ip());

        $key = 'too-many-requests:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 4)) {
            $retryAfter = RateLimiter::availableIn($key);

            return response()->json(['message' => 'Too many requests. Please try again later.'], 429)
                ->header('Retry-After', $retryAfter);
        }

        RateLimiter::hit($key, 60);

        $this->debugMess('requests limit validate complete');
    }

    private function debugMess($mess)
    {
        //запись в логи. а так же в консоль, если включен режим отладки(задается константой в контролере)

        Log::info($mess);
        if (DEBUG_MOD) dump("debug info: $mess"); //вся отладка для консоли производится в этой функции, т.к. ошибки обрабатываются в других местах, и я пока не нашел адекватное применение отладочной информации
    }

    private function getAccountIdByToken($tokenValue)
    {
        $token = Token::where('value', $tokenValue)->first();

        if ($token) $this->debugMess("currect user is ".Account::find($token->account_id)->name);
        else $this->debugMess("token not be init");
        
        return optional($token)->account_id;
    }

    function getData($accountId, $table, $days=false, $limit=false)
    {
        $query = DB::table($table)->where('account_id', $accountId);

        if ($days !== false) {
            $query->where('date', '>=', Carbon::now()->subDays($days)->format('Y-m-d H:i:s'));
        }

        if ($limit !== false) {
            $query->limit($limit);
        }

        return $query->get();
    }

}