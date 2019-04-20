<?php
namespace App\Frontend\Modules\News;

use \OCFram\BackController;
use \OCFram\HTTPRequest;
use \Entity\Comment;
use \FormBuilder\CommentFormBuilder;
use \OCFram\FormHandler;
use \OCFram\Cache;

class NewsController extends BackController implements \SplObserver
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
        $cacheNews = new Cache($this->app, '\\datas\\news-' . $request->getData('id'));
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

        $cacheComments = new Cache($this->app, '\\datas\\comments-' . $request->getData('id'));

        if ($cacheComments->isValid()){
            $listOfComments = explode(PHP_EOL, $cacheComments->getContent());
            $allCommentsObject = [];
            foreach($listOfComments  as $cacheCommentContent){
                if (!empty($cacheCommentContent)){
                    $allCommentsObject[] = unserialize($cacheCommentContent);
                }
            }
            $this->page->addVar('comments', $allCommentsObject);
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

        $manager = $this->managers->getManagerOf('Comments');
        $manager->attach($this);

        $formHandler = new FormHandler($form, $manager, $request);

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

        return $viewsToCache;
    }

    public function update(\SplSubject $obj){}

    public function update2($element, $news){
        if ($element == 'comments'){
            $cache = new Cache($this->app, '\\datas\\comments-' . $news);
            $cache->removeCache();
        }
    }
}