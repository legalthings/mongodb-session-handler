<?php

namespace LegalThings;

/**
 * A class that implements SessionHandlerInterface and can be used to store sessions as structured data in MongoDB
 */
class MongodbSessionHandler implements \SessionHandlerInterface
{
    /**
     * Session collection
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * Never store changes to a session
     * @var boolean
     */
    protected $readonly = false;


    /**
     * Class constructor
     * 
     * @param \MongoCollection $collection
     * @param string           $mode        'w' for read-write or 'r' for read-only
     */
    public function __construct(\MongoCollection $collection, $mode = 'w')
    {
        $this->collection = $collection;
        $this->readonly = ($mode === 'r');
    }
    
    /**
     * Initialize session
     * @link http://php.net/manual/en/sessionhandlerinterface.open.php
     * 
     * @param string $save_path  The path where to store/retrieve the session.
     * @param string $name       The session name.
     * @return boolean
     */
    public function open($save_path, $name)
    {
    }

    /**
     * Close the session
     * @link http://php.net/manual/en/sessionhandlerinterface.close.php
     * 
     * @return boolean
     */
    public function close()
    {
        unset($_SESSION);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Read session data
     * @link http://php.net/manual/en/sessionhandlerinterface.read.php
     * @param string $session_id  The session id.
     * @return string
     */
    public function read($session_id)
    {
        $values = $this->collection->findOne(['_id' => $session_id]);
        unset($values['_id']);

        foreach ($values as $key => $value) {
            $_SESSION[$key] = $value;
        }

        return false;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Write session data
     * @link http://php.net/manual/en/sessionhandlerinterface.write.php
     * @param string $session_id    The session id.
     * @param string $session_data  The encoded session data.
     * @return boolean
     */
    public function write($session_id, $session_data)
    {
        if ($this->readonly) {
            return;
        }
        
        $this->collection->save(['_id' => $session_id] + $_SESSION);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Destroy a session
     * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
     * @param string $session_id The session ID being destroyed.
     * @return boolean
     */
    public function destroy($session_id)
    {
        if ($this->readonly) {
            return;
        }
        
        $this->collection->remove(['_id' => $session_id]);
    }

    /**
     * This method must be implemented for the interface, but isn't used. Instead use a MongoDB tty index.
     * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
     * @link https://docs.mongodb.com/manual/core/index-ttl/
     * 
     * @param int $maxlifetime
     */
    public function gc($maxlifetime)
    {
    }
    
    /**
     * This callback is executed when a new session ID is required. No parameters are provided, and the return value
     * should be a string that is a valid session ID for your handler.
     * 
     * @return string
     */
    public function create_sid()
    {
        $hex = bin2hex(random_bytes(16));
        $id = base_convert($hex, 16, 36);
        
        return sprintf('%024s', substr($id, -24));
    }
}
