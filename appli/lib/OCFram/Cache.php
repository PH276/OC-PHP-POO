<?php
namespace OCFram;

class Cache extends ApplicationComponent{
    protected  $valid = false;
    protected  $dateExpiry = null;
    protected  $content = '';
    protected  $delai = 0;
    protected $type = '';
    protected  $filename='';

    public function __construct(Application $app, $type, $filename)
    {
        parent::__construct($app);
        $this->type = $type;
        $this->filename = $filename;
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
        fwrite ($id_file, $this->content.PHP_EOL);
        fclose($id_file);
    }

    public function removeCache(){
        $this->dateExpiry = null;
        $this->content = '';
        unlink($this->filename);
    }

}
?>