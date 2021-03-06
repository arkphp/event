<?php

/*
 * This file is part of Evenement.
 *
 * (c) Igor Wiedler <igor@wiedler.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ark\Event\Tests;

use Ark\Event\EventEmitter;

class Listener
{
    public function onFoo()
    {
    }
    public static function onBar()
    {
    }
}

class EventEmitterTest extends \PHPUnit_Framework_TestCase
{
    private $emitter;

    public function setUp()
    {
        $this->emitter = new EventEmitter();
    }

    public function testAddListenerWithLambda()
    {
        $this->emitter->on('foo', function () {});
    }

    public function testAddListenerWithMethod()
    {
        $listener = new Listener();
        $this->emitter->on('foo', [$listener, 'onFoo']);
    }

    public function testAddListenerWithStaticMethod()
    {
        $this->emitter->on('bar', ['Ark\Event\Tests\Listener', 'onBar']);
    }

    public function testAddListenerWithInvalidListener()
    {
        try {
            $this->emitter->on('foo', 'not a callable');
            $this->fail();
        } catch (\Exception $e) {
        }
    }

    public function testOnce()
    {
        $listenerCalled = 0;

        $this->emitter->once('foo', function () use (&$listenerCalled) {
            $listenerCalled++;
        });

        $this->assertSame(0, $listenerCalled);

        $this->emitter->emit('foo');

        $this->assertSame(1, $listenerCalled);

        $this->emitter->emit('foo');

        $this->assertSame(1, $listenerCalled);
    }

    public function testOnceWithArguments()
    {
        $capturedArgs = [];

        $this->emitter->once('foo', function ($a, $b) use (&$capturedArgs) {
            $capturedArgs = array($a, $b);
        });

        $this->emitter->emit('foo', array('a', 'b'));

        $this->assertSame(array('a', 'b'), $capturedArgs);
    }

    public function testEmitWithoutArguments()
    {
        $listenerCalled = false;

        $this->emitter->on('foo', function () use (&$listenerCalled) {
            $listenerCalled = true;
        });

        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo');
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithOneArgument()
    {
        $test = $this;

        $listenerCalled = false;

        $this->emitter->on('foo', function ($value) use (&$listenerCalled, $test) {
            $listenerCalled = true;

            $test->assertSame('bar', $value);
        });

        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo', ['bar']);
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithTwoArguments()
    {
        $test = $this;

        $listenerCalled = false;

        $this->emitter->on('foo', function ($arg1, $arg2) use (&$listenerCalled, $test) {
            $listenerCalled = true;

            $test->assertSame('bar', $arg1);
            $test->assertSame('baz', $arg2);
        });

        $this->assertSame(false, $listenerCalled);
        $this->emitter->emit('foo', ['bar', 'baz']);
        $this->assertSame(true, $listenerCalled);
    }

    public function testEmitWithNoListeners()
    {
        $this->emitter->emit('foo');
        $this->emitter->emit('foo', ['bar']);
        $this->emitter->emit('foo', ['bar', 'baz']);
    }

    public function testEmitWithTwoListeners()
    {
        $listenersCalled = 0;

        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(2, $listenersCalled);
    }

    public function testRemoveListenerMatching()
    {
        $listenersCalled = 0;

        $listener = function () use (&$listenersCalled) {
            $listenersCalled++;
        };

        $this->emitter->on('foo', $listener);
        $this->emitter->off('foo', $listener);

        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(0, $listenersCalled);
    }

    public function testRemoveListenerNotMatching()
    {
        $listenersCalled = 0;

        $listener = function () use (&$listenersCalled) {
            $listenersCalled++;
        };

        $this->emitter->on('foo', $listener);
        $this->emitter->off('bar', $listener);

        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenersCalled);
    }

    public function testRemoveAllListenersMatching()
    {
        $listenersCalled = 0;

        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->emitter->off('foo');

        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(0, $listenersCalled);
    }

    public function testRemoveAllListenersNotMatching()
    {
        $listenersCalled = 0;

        $this->emitter->on('foo', function () use (&$listenersCalled) {
            $listenersCalled++;
        });

        $this->emitter->off('bar');

        $this->assertSame(0, $listenersCalled);
        $this->emitter->emit('foo');
        $this->assertSame(1, $listenersCalled);
    }

    public function testPriority()
    {
        $data = [];
        $this->emitter->on('priority', function () use (&$data) {
            $data[] = 'low';
        }, 100);

        $this->emitter->on('priority', function () use (&$data) {
            $data[] = 'high';
        }, 0);

        $this->emitter->emit('priority');
        $this->assertEquals(['high', 'low'], $data);
    }

    public function testPropagation()
    {
        $data = [];
        $this->emitter->on('prop', function () use (&$data) {
            $data[] = 'a';
        }, 100);

        $this->emitter->on('prop', function () use (&$data) {
            $data[] = 'b';

            return false;
        }, 0);

        $this->emitter->emit('prop');
        $this->assertEquals(['b'], $data);
    }
}
