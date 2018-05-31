<?php

namespace LegalThings;

use LegalThings\MongodbSessionHandler;

/**
 * @covers LegalThings\MongodbSessionHandler
 */
class MongodbSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        session_cache_limiter('');
        ini_set('session.use_cookies', 0);
        ini_set('session.use_only_cookies', 0);

        session_start();
    }
    
    public function tearDown()
    {
        $void = function() {};
        session_set_save_handler($void, $void, $void, $void, $void, $void, $void);
        session_abort();
    }
    
    
    public function testOpen()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $handler->open('', '');
    }
    
    public function testClose()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $handler->close();
        
        $this->assertFalse(isset($_SESSION));
    }
    
    public function testRead()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $collection->expects($this->once())->method('findOne')
            ->with(['_id' => '1234'])
            ->willReturn(['_id' => '1234', 'foo' => 'bar', 'zoo' => 'ram']);
        
        $handler->read('1234');
        $this->assertEquals(['foo' => 'bar', 'zoo' => 'ram'], $_SESSION);
    }
    
    public function testReadNotFound()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $collection->expects($this->once())->method('findOne')
            ->with(['_id' => '9876'])
            ->willReturn(null);
        
        $handler->read('9876');
        $this->assertEquals([], $_SESSION);
    }
    
    public function testWrite()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $collection->expects($this->once())->method('insertOne')
            ->with(['_id' => '1234', 'foo' => 'bar']);
        
        $_SESSION['foo'] = 'bar';
        $handler->write('1234', serialize(['not' => 'used']));
    }
    
    public function testWriteReadOnly()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection, 'r');
        
        $collection->expects($this->never())->method('insertOne');
        
        $_SESSION['foo'] = 'bar';
        $handler->write('1234', serialize(['not' => 'used']));
    }
    
    public function testDestroy()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $collection->expects($this->once())->method('deleteOne')
            ->with(['_id' => '1234']);
        
        $_SESSION['foo'] = 'bar';
        $handler->destroy('1234');
    }
    
    public function testDestroyReadOnly()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection, 'r');
        
        $collection->expects($this->never())->method('deleteOne');
        
        $_SESSION['foo'] = 'bar';
        $handler->destroy('1234');
    }
    
    public function testGc()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $handler->gc(3600);
    }
    
    public function testCreateSid()
    {
        $collection = $this->createMock(\MongoDB\Collection::class);
        $handler = new MongodbSessionHandler($collection);
        
        $sid = $handler->create_sid();
        $this->assertRegExp('/^[0-9a-z]{24}$/', $sid);
    }
}
