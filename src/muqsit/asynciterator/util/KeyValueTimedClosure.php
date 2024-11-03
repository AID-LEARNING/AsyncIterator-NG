<?php

declare(strict_types=1);

namespace muqsit\asynciterator\util;

use Closure;
use Generator;
use pocketmine\timings\TimingsHandler;
use SOFe\AwaitGenerator\Await;

/**
 * @template T
 * @template U
 * @template V
 */
final class KeyValueTimedClosure{

	/**
	 * @param TimingsHandler $timings
	 * @param Closure(T, U) : V $closure
	 */
	public function __construct(
		readonly private TimingsHandler $timings,
		readonly public Closure $closure
	){}

	/**
	 * @param T $key
	 * @param U $value
	 * @return V
	 */
	public function call(mixed $key, mixed $value) : mixed{
		$this->timings->startTiming();
		$return = ($this->closure)($key, $value);
		$this->timings->stopTiming();
		return $return;
	}

	public function callAsync(mixed $key, mixed $value) : Generator
	{
		return Await::promise(function($resolve) use($key, $value){
			Await::f2c(function() use($key, $value){
				$this->timings->startTiming();
				$return = yield from ($this->closure)($key, $value);
				$this->timings->stopTiming();
				return $return;
			}, $resolve);
		});
	}
}