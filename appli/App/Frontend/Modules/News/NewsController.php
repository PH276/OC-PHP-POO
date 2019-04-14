<?php
namespace App\Frontend\Modules\News;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \OCFram\FormHandler;
use \OCFram\Cache;

class NewsController extends BackController
{
    public function executeIndex(HTTPRequest $request)
    {
        $nombreNews = $this->app->config()->get('nombre_news');
        $nombreCaracteres = $this->app->config()->get('nombre_caracteres');

        // On ajoute une définition pour le titre.
        $this->page->addVar('title', 'Liste des '.$nombreNews.' dernières news');

        // On récupère le manager des news.
        $manager = $this->managers->getManagerOf('News');

        $listeNews = $manager->getList(0, $nombreNews);

        foreach ($listeNews as $news)
        {
            if (strlen($news->contenu()) > $nombreCaracteres)
            {
                $debut = substr($news->contenu(), 0, $nombreCaracteres);
                $debut = substr($debut, 0, strrpos($debut, ' ')) . '...';

                $news->setContenu($debut);
            }
        }

        // On ajoute la variable $listeNews à la vue.
        $this->page->addVar('listeNews', $listeNews);
    }

    public function executeShow(HTTPRequest $request)
    {
        $path_cache = __DIR__ . '\\..\\..\\..\\..\\' .$this->app->config()->get('path_cache');
        $filenameNews = $path_cache . '\\datas\\views-' . $request->getData('id');
        $cacheNews = new Cache($this->app, 'datas', $filenameNews);
        if ($cacheNews->isValid()){
            $news = unserialize($cacheNews->getContent());
        }
        else{

            $news = $this->managers->getManagerOf('News')->getUnique($request->getData('id'));
            $cacheNews->setDate(3 * 86400);
            $cacheNews->setContent(serialize($news));
            $cacheNews->genereCache();
        }

        if (empty($news))
        {
            $this->app->httpResponse()->redirect404();
        }

        $this->page->addVar('title', $news->titre());
        $this->page->addVar('news', $news);

        $filenameComments = $path_cache . '\\datas\\comments-' . $request->getData('id');
        $cacheComments = new Cache($this->app, 'datas', $filenameComments);

        if ($cacheComments->isValid()){
            $litOfComments = explode(PHP_EOL, $cacheComments->getContent());
            $allComments = [];
            foreach($litOfComments  as $cacheCommentContent){
                if (!empty($cacheCommentContent)){
                    $allComments[] = unserialize($cacheCommentContent);
                }
            }
            $this->page->addVar('comments', $allComments);
        }
        else{

            $listOfComments = $this->managers->getManagerOf('Comments')->getListOf($news->id());
            $this->page->addVar('comments', $listOfComments);
            $cacheComments->setDate(3 * 86400);
            $commentsSerialize = '';
            foreach($listOfComments as $comment){
                $commentsSerialize .= serialize($comment) . PHP_EOL;
            }
            $cacheComments->setContent($commentsSerialize);
            $cacheComments->genereCache();
        }



    }

    public function executeInsertComment(HTTPRequest $request)
    {
        // Si le formulaire a été envoyé.
        if ($request->method() == 'POST')
        {
            $comment = new Comment([
                'news' => $request->getData('news'),
                'auteur' => $request->postData('auteur'),
                'contenu' => $request->postData('contenu')
            ]);
        }
        else
        {
            $comment = new Comment;
        }

        $formBuilder = new CommentFormBuilder($comment);
        $formBuilder->build();

        $form = $formBuilder->form();

        $formHandler = new FormHandler($form, $this->managers->getManagerOf('Comments'), $request);

        if ($formHandler->process())
        {
            $this->app->user()->setFlash('Le commentaire a bien été ajouté, merci !');

            $this->app->httpResponse()->redirect('news-'.$request->getData('news').'.html');
        }

        $this->page->addVar('comment', $comment);
        $this->page->addVar('form', $form->createView());
        $this->page->addVar('title', 'Ajout d\'un commentaire');
    }

    public function createCache()
    {
        $viewsToCache = ['index' => '3'];
        $validedCache = false;

        $viewToCache = (array_key_exists($this->view, $viewsToCache))?true:false;
        if  ($viewToCache){
            $path_cache = __DIR__ . '\\..\\..\\..\\..\\' .$this->app->config()->get('path_cache');
            $filename = $path_cache . '\\views\\' . $this->app->name() . '_' . $this->module . '_' . $this->view;
            $cache = new Cache($this->app, 'views', $filename);
            $validedCache = $cache->isValid();
            if ($validedCache){
                $this->page->setContentView($cache->getContent());
            }else{
                $this->execute();
                $this->page->genereView();
                $cache->setDate($viewsToCache[$this->view] * 86400);
                $cache->setContent($this->page->getContentView());
                $cache->genereCache();
            }

        }else
        {
            $this->execute();
            $this->page->genereView();
        }

    }

}