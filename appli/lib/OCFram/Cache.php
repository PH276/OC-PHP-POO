<?php
namespace OCFram;

class Cache extends ApplicationComponent{
    protected  $valid = false;
    protected  $dateExpiry = null;
    protected  $content = '';
    protected  $delai = 0;
    protected  $filename = '';

    const SECONDES_JOUR = 86400;

    public function __construct(Application $app, $filename)
    {
        parent::__construct($app);
        $path_cache = __DIR__ . '\\..\\..\\' .$app->config()->get('path_cache');
        $this->filename = $path_cache . $filename;
    }

    public function setDate($delay) {
        $this->dateExpiry = time() + $delay;
    }

    public function setContent($content) {
        $this->content = $content;
    }

    public function getContent() {
        return $this->content;
    }

    public function isValid() {

        if (file_exists($this->filename)){
            $file = file($this->filename);
            $this->dateExpiry = $file[0];
            if ($this->dateExpiry > time()){
                $this->valid = true;
                $contentFile = array_slice($file,1);
                foreach ($contentFile as $line){
                    $this->content .= $line;
                }
            }else{
                $this->removeCache();
            }
        }
        return $this->valid;
    }

    public function genereCache(){
        $id_file = fopen($this->filename, 'w');
        fwrite ($id_file, $this->dateExpiry.PHP_EOL);
        fwrite ($id_file, $this->content);
        fclose($id_file);
    }

    public function removeCache(){
        if (file_exists($this->filename)){
            unlink($this->filename);
        }
    }

}
?>