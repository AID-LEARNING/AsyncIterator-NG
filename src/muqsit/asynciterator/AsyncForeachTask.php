<?php

declare(strict_types=1);

namespace muqsit\asynciterator;

use Generator;
use muqsit\asynciterator\handler\AsyncForeachHandler;
use muqsit\asynciterator\handler\SimpleAsyncForeachHandlerGenerator;
use pocketmine\scheduler\Task;
use SOFe\AwaitGenerator\Await;

/**
 * @template TKey
 * @template TValue
 */
final class AsyncForeachTask extends Task{

	/**
	 * @param AsyncForeachHandler<TKey, TValue> $async_foreach_handler
	 */
	private bool $running = false;
	public function __construct(
		readonly private AsyncForeachHandler $async_foreach_handler
	){}

	public function onRun() : void{
		if ($this->running)
			return;
		if($this->async_foreach_handler instanceof SimpleAsyncForeachHandlerGenerator){
			Await::f2c(function (): Generator {
				$this->running = true;
				return yield from $this->async_foreach_handler->handle();
			}, function (bool $handle) {
				$this->running  = false;
				if(!$handle){
					$this->async_foreach_handler->doCompletion();
					$task_handler = $this->getHandler();
					if($task_handler !== null){
						$task_handler->cancel();
					}
				}
			}, function () {
				$this->running = false;
				$this->async_foreach_handler->interrupt();
			});
			return;
		}
		if(!$this->async_foreach_handler->handle()){
			$this->async_foreach_handler->doCompletion();
			$task_handler = $this->getHandler();
			if($task_handler !== null){
				$task_handler->cancel();
			}
		}
	}
}