<?php

namespace LegalThings;

use LegalThings\MongodbSessionHandler;

/**
 * @coversNothing
 */
class FunctionalTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        session_cache_limiter('');
        ini_set('session.use_cookies', 0);
        ini_set('session.use_only_cookies', 0);
    }
    
    public function tearDown()
    {
        $void = function() {};
        session_set_save_handler($void, $void, $void, $void, $void, $void, $void);
        session_abort();
    }
    
    
    public function testStartCommit()
    {
        session_cache_limiter('');
        ini_set('session.use_cookies', 0);
        ini_set('session.use_only_cookies', 0);
        
        $collection = $this->createMock(\MongoCollection::class);

        $collection->expects($this->once())->method('findOne')
            ->with(['_id' => '1234'])
            ->willReturn(['_id' => '1234', 'foo' => 'bar', 'zoo' => 'ram']);
        
        $collection->expects($this->once())->method('save')
            ->willReturn(['_id' => '1234', 'foo' => 'bar', 'zoo' => 'ram', 'col' => 'pan']);
        
        $handler = new MongodbSessionHandler($collection);
        session_set_save_handler($handler);
        
        session_id('1234');
        session_start();
        
        $this->assertSame($_SESSION, ['foo' => 'bar', 'zoo' => 'ram']);
        
        $_SESSION['zoo'] = 'moo';
        $_SESSION['col'] = 'pan';
        session_write_close();
        
        $this->assertFalse(isset($_SESSION));
    }
    
    public function testStartAbort()
    {
        session_cache_limiter('');
        ini_set('session.use_cookies', 0);
        ini_set('session.use_only_cookies', 0);
        
        $collection = $this->createMock(\MongoCollection::class);

        $collection->expects($this->once())->method('findOne')
            ->with(['_id' => '1234'])
            ->willReturn(['_id' => '1234', 'foo' => 'bar', 'zoo' => 'ram']);
        
        $collection->expects($this->never())->method('save');
        
        $handler = new MongodbSessionHandler($collection);
        session_set_save_handler($handler);
        
        session_id('1234');
        session_start();
        
        $this->assertSame($_SESSION, ['foo' => 'bar', 'zoo' => 'ram']);
        
        $_SESSION['zoo'] = 'moo';
        session_abort();
        
        $this->assertFalse(isset($_SESSION));
    }
    
}