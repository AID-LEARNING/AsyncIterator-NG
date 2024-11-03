<?php

declare(strict_types=1);

namespace muqsit\asynciterator;

use muqsit\asynciterator\handler\AsyncForeachHandler;
use muqsit\asynciterator\handler\SimpleAsyncForeachHandler;
use Iterator;
use muqsit\asynciterator\handler\SimpleAsyncForeachHandlerGenerator;
use pocketmine\scheduler\TaskScheduler;

class AsyncIterator{

	public function __construct(
		readonly private TaskScheduler $scheduler
	){}

	/**
	 * @template TKey
	 * @template TValue
	 * @param Iterator<TKey, TValue> $iterable
	 * @param int $entries_per_tick
	 * @param int $sleep_time
	 * @return AsyncForeachHandler<TKey, TValue>
	 */
	public function forEach(Iterator $iterable, int $entries_per_tick = 10, int $sleep_time = 1) : AsyncForeachHandler{
		$handler = new SimpleAsyncForeachHandler($iterable, $entries_per_tick);
		$task_handler = $this->scheduler->scheduleDelayedRepeatingTask(new AsyncForeachTask($handler), 1, $sleep_time);
		$handler->init("Plugin: {$task_handler->getOwnerName()} Event: AsyncIterator");
		return $handler;
	}

	/**
	 * @template TKey
	 * @template TValue
	 * @param Iterator<TKey, TValue> $iterable
	 * @param int $entries_per_tick
	 * @param int $sleep_time
	 * @return AsyncForeachHandler<TKey, TValue>
	 */
	public function forEachGenerator(Iterator $iterable, int $entries_per_tick = 10, int $sleep_time = 1) : AsyncForeachHandler{
		$handler = new SimpleAsyncForeachHandlerGenerator($iterable, $entries_per_tick);
		$task_handler = $this->scheduler->scheduleDelayedRepeatingTask(new AsyncForeachTask($handler), 1, $sleep_time);
		$handler->init("Plugin: {$task_handler->getOwnerName()} Event: AsyncIterator");
		return $handler;
	}
}