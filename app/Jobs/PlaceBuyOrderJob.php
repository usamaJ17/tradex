<?php

namespace App\Jobs;

use App\Http\Services\BuyOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PlaceBuyOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $timeout = 0;
    private $data;
    private $userId;
    public function __construct($data,$userId)
    {
        $this->data = $data;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = new Request($this->data);
        $service = new BuyOrderService();
        if (isset($request->is_market) && $request->is_market == 0) {
            $service->_passiveBuyOrder($request, $this->userId);
        } else {
            $service->_activeBuyOrder($request, $this->userId);
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        storeException('PlaceSellOrderJob failed',json_encode($exception));
        // Send user notification of failure, etc...
    }
}
