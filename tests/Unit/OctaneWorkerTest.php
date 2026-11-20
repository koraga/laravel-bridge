<?php

declare(strict_types=1);

namespace Spiral\RoadRunnerLaravel\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\Worker;
use Spiral\RoadRunnerLaravel\OctaneWorker;
use Testo\Assert;
use Testo\Lifecycle\AfterTest;
use Testo\Test;

#[Test]
final class OctaneWorkerTest
{
    public function test_boot_captures_request_that_octane_restores_after_each_request(): void
    {
        $app = new Application();
        $app->instance('request', $request = Request::create('/'));

        $worker = new OctaneWorker(new ApplicationFactory(__DIR__));
        $worker->boot(application: $app);

        Assert::same($this->bootRequest($worker), $request);
    }

    public function test_boot_does_not_require_bound_request(): void
    {
        $worker = new OctaneWorker(new ApplicationFactory(__DIR__));
        $worker->boot(application: new Application());

        Assert::null($this->bootRequest($worker));
    }

    #[AfterTest]
    public function resetContainer(): void
    {
        Container::setInstance(null);
    }

    private function bootRequest(OctaneWorker $worker): ?Request
    {
        /** @var Request|null */
        return (new \ReflectionProperty(Worker::class, 'bootRequest'))->getValue($worker);
    }
}
