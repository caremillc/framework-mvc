<?php 
namespace Careminate\Sessions;

use SessionHandlerInterface;


class SessionHandler implements SessionHandlerInterface
{
   
        
    /**
     * __construct
     *
     * @param  mixed $save_path
     * @param  mixed $prefix
     * @return void
     */
    public function __construct(public string $save_path, public string $prefix)
    {
    }

    /**
     * @return bool
     */
     public function close(): bool
    {
        return true;
    }

    /**
     * @param string $id
     * 
     * @return bool
     */
    public function destroy(string $id): bool
    {
        $file = $this->save_path . DIRECTORY_SEPARATOR . $this->prefix . '_' . $id;
        if (file_exists($file)) {
            unlink($file);
        }
        return true;
    }
    
    
    /**
     * @param int $max_lifetime
     * 
     * @return int
     */
      public function gc(int $max_lifetime): int|false
        {
            $deleted = 0;
            foreach (glob($this->save_path . '/' . $this->prefix . '_*') as $file) {
                if (filemtime($file) + $max_lifetime < time() && file_exists($file)) {
                    unlink($file);
                    $deleted++;
                }
            }
            return $deleted;
        }

    /**
     * @param string $path
     * @param string $name
     * 
     * @return bool
     */
     public function open(string $path, string $name): bool
    {
        if (!is_dir($this->save_path)) {
            mkdir($this->save_path, 0755, true);
        }
        return true;
    }


    /**
     * @param string $id
     * 
     * @return string
     */
       public function read(string $id): string|false
        {
            $file = $this->save_path . '/' . $this->prefix . '_' . $id;
            return file_exists($file) ? file_get_contents($file) : '';
        }


    /**
     * @param string $id
     * @param string $data
     * 
     * @return bool
     */
     public function write(string $id, string $data): bool
    {
        return file_put_contents($this->save_path . '/' . $this->prefix . '_' . $id, $data) !== false;
    }
}



