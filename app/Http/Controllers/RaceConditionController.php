<?php

namespace App\Http\Controllers;

use App\Models\Increment;
use App\Models\LogIncrement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RaceConditionController extends Controller
{
    public function mask_process()
    {

        for ($i = 1; $i < 7; $i++) {
            $jobs['process_' . $i] = $this->handle_jobs('process_' . $i);
        }
        return $jobs;
    }
    public function process_1()
    {
        return $this->handle_jobs('process_1');
    }
    public function process_2()
    {
        return $this->handle_jobs('process_2');
    }
    public function process_3()
    {
        return $this->handle_jobs('process_3');
    }
    public function process_4()
    {
        return $this->handle_jobs('process_4');
    }
    public function process_5()
    {
        return $this->handle_jobs('process_5');
    }
    public function process_6()
    {
        return $this->handle_jobs('process_6');
    }

    public function process_count()
    {
        $process_count = LogIncrement::query()
            ->select(DB::raw('count(*) as total'), 'process')
            ->groupBy('process')
            ->get();

        return $process_count;
    }

    public function handle_jobs($process_name, $entries = [])
    {
        $count = 50;

        if (count($entries) > 0) {
            $count = count($entries);
        }

        unset($duplicate_entry);
        $duplicate_entry = [];

        try {
            for ($i = 0; $i < $count; $i++) {
                $this->ms_sleep(50); /* if you want to delay your process */
                $duplicate_entry[] = $this->increment($process_name);
            }
        } catch (\Throwable $th) {
            throw $th;
        } finally {
            $add_to_entry = [];
            if (count($duplicate_entry) > 0) {
                foreach ($duplicate_entry as $key => $value) {
                    if (isset($value) && $value !== null) {
                        $add_to_entry[] = $value['process'];
                    }
                }
            }

            if (count($add_to_entry) > 0) {
                return $this->handle_jobs($process_name, $add_to_entry);
            } else {
                return 'process done, at ' . Carbon::now()->format('Y-m-d H:i:s');
            }
        }
    }

    public function ms_sleep($milliseconds = 0)
    {
        if ($milliseconds > 0) {
            $test = $milliseconds / 1000;
            $seconds = floor($test);
            $micro = round(($test - $seconds) * 1000000);
            if ($seconds > 0) sleep($seconds);
            if ($micro > 0) usleep($micro);
        }
    }

    public function missing_value()
    {
        $log_increment = LogIncrement::query()->get()->pluck('value')->toArray();

        $missing_value = [];
        for ($i = 1; $i <= 200; $i++) {
            if (!in_array($i, $log_increment)) {
                $missing_value[] = $i;
            }
        }

        return $missing_value;
    }

    public function double_value()
    {
        $log_increment = LogIncrement::query()
            ->select(DB::raw('count("value") as value_count'), 'process', 'value')
            ->groupBy('value')
            ->having('value_count', '>', 1)
            ->get();

        return $log_increment;
    }

    public function increment($process_name)
    {

        DB::beginTransaction();
        try {
            $increment = Increment::query();

            if ($increment->count() > 0) {
                $increment = $increment->latest()->first();

                // update auto increment
                $counter = $increment->counter;
                $increment->counter = ($counter + 1);
                $increment->save();
                $increment->fresh();

                // insert new log for auto increment
                LogIncrement::query()
                    ->create([
                        'process' => $process_name,
                        'value' => $increment->counter,
                    ]);
            } else {
                $increment->create([
                    'counter' => 0,
                ]);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            $errorCode = $th->errorInfo[1];
            if ($errorCode == '1062') {
                $duplicate_entry = [
                    'process' => $process_name,
                    'value' => $increment->counter,
                ];

                return $duplicate_entry;
            } else {
                throw $th;
            }
        }
    }
}
