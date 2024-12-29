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
use GuzzleHttp\Promise;


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
        if (!$dateFrom) $dateFrom = '2000-01-01';
        if (!$dateTo) $dateTo = date('Y-m-d');
        if (!$key) $key = All::getPrivateKey();

        $isLastPage = false;
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

                $accountId = null;//$this->getAccountIdByToken($key);
                $this->addAccountIdInData($data, $accountId);
                Sale::insert($data);
                gc_collect_cycles();//!!!!без этой хуйни все падает с превышением выделенной памяти. 
                /*foreach ($data as $saleData) {
                    $sales = [
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
                    Sale::insert($sales);
                }*/
                
            } else if ($response->status() === 429) {
                $retryAfter = $response->header('Retry-After', 1); // Получаем время ожидания, если указано, или 1 секунда по умолчанию
                echo "429_$retryAfter\n";
                sleep($retryAfter); // Ждем указанное время перед повторной попыткой
                continue; // Переходим к следующей итерации циклаreturn response()->json(['message' => 'unsucc'], 500);
            } else return response()->json(['message' => 'unsucc response'], 500);

            print "$page|"; if ($page % 10 === 0) print "<br>\n";
            if ($page++ === $response->json()['meta']['last_page']) $isLastPage = true;
        }
        print "<hr>!";print $response->json()['meta']['last_page'];
        return response()->json(['message' => 'succ']);

        
    }

    public function fetchIncomes(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $key = $request->input('key');
        if (!$key) $key = All::getPrivateKey();

        return $this->add('incomes', $key, $request);
    }

    public function fetchStocks(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $key = $request->input('key');
        if (!$key) $key = All::getPrivateKey();

        return $this->add('stocks', $key, $request);
    }

    public function fetchOrders(Request $request)
    {
        if ($errResponse = $this->handleRateLimiting($request)) return $errResponse;

        $key = $request->input('key');
        if (!$key) $key = All::getPrivateKey();

        return $this->add('orders', $key, $request);
    }

    private function add($table, $token, Request $request)
    {
        $accountId = $this->getAccountIdByToken($token);
        DB::table($table)->where('account_id', $accountId)->delete(); //fresh

        $dateFrom = $request->input('dateFrom');
        $dateTo = $request->input('dateTo');
        $page = $request->input('page');
        $limit = $request->input('limit');
        $key = $request->input('key');

        if (!$page) $page = 1;
        if (!$limit) $limit = 500;
        if (!$dateFrom) $dateFrom =  ($table === 'stocks') ? date('Y-m-d') : '2000-01-01';
        if (!$dateTo) $dateTo = date('Y-m-d');
        if (!$key) $key = All::getPrivateKey();

        $isLastPage = false;
        while (!$isLastPage) {
            $response = Http::get("http://89.108.115.241:6969/api/$table", [
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'page' => $page,
                'key' => $key,
                'limit' => $limit,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'];
                $this->addAccountIdInData($data, $accountId);

                DB::table($table)->insert($data);
                gc_collect_cycles();//!!!!без этой хуйни все падает с превышением выделенной памяти. 
            } else if ($response->status() === 429) {
                $retryAfter = $response->header('Retry-After', 1); // Получаем время ожидания, если указано, или 1 секунда по умолчанию
                echo "!429_$retryAfter\n";
                sleep($retryAfter); // Ждем указанное время перед повторной попыткой
                continue; // Переходим к следующей итерации циклаreturn response()->json(['message' => 'unsucc'], 500);
            }else return response()->json(['message' => 'unsucc response'], 500);
            print "$page|";if ($page % 10 === 0) print '<br>'; //!

            if ($page++ === $response->json()['meta']['last_page']) $isLastPage = true;
        }
        
        return response()->json(['message' => 'succ']);
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

        if ($token) $this->debugMess("request to api from account ".Account::find($token->account_id)->name);
        else $this->debugMess("request to api from anon account. user_id is null");
        
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