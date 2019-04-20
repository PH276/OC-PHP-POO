<?php
namespace Model;

use \Entity\Comment;

class CommentsManagerPDO extends CommentsManager implements \SplSubject
{
    // Ceci est le tableau qui va contenir tous les objets qui nous observent.
    protected $observers = [];

    protected function add(Comment $comment)
    {
        $q = $this->dao->prepare('INSERT INTO comments SET news = :news, auteur = :auteur, contenu = :contenu, date = NOW()');

        $q->bindValue(':news', $comment->news(), \PDO::PARAM_INT);
        $q->bindValue(':auteur', $comment->auteur());
        $q->bindValue(':contenu', $comment->contenu());

        $q->execute();

        $comment->setId($this->dao->lastInsertId());

        $this->notify2($comment->news());
    }

    public function delete($id)
    {
        $comment = $this->get($id);
        $this->dao->exec('DELETE FROM comments WHERE id = '.(int) $id);

        $this->notify2($comment->news());
    }

    public function deleteFromNews($news)
    {
        $this->dao->exec('DELETE FROM comments WHERE news = '.(int) $news);

        $this->notify2($news);
    }

    public function getListOf($news)
    {
        if (!ctype_digit($news))
        {
            throw new \InvalidArgumentException('L\'identifiant de la news passé doit être un nombre entier valide');
        }

        $q = $this->dao->prepare('SELECT id, news, auteur, contenu, date FROM comments WHERE news = :news');
        $q->bindValue(':news', $news, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\Comment');

        $comments = $q->fetchAll();

        foreach ($comments as $comment)
        {
            $comment->setDate(new \DateTime($comment->date()));
        }

        return $comments;
    }

    protected function modify(Comment $comment)
    {
        $q = $this->dao->prepare('UPDATE comments SET auteur = :auteur, contenu = :contenu WHERE id = :id');

        $q->bindValue(':auteur', $comment->auteur());
        $q->bindValue(':contenu', $comment->contenu());
        $q->bindValue(':id', $comment->id(), \PDO::PARAM_INT);

        $q->execute();

        $this->notify2($comment->news());
    }

    public function get($id)
    {
        $q = $this->dao->prepare('SELECT id, news, auteur, contenu FROM comments WHERE id = :id');
        $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\Comment');

        return $q->fetch();
    }

    public function attach(\SplObserver $observer)
    {
        $this->observers[] = $observer;
    }

    public function detach(\SplObserver $observer)
    {
        if (is_int($key = array_search($observer, $this->observers, true)))
        {
            unset($this->observers[$key]);
        }
    }

    public function notify(){}

    public function notify2($news){
        foreach ($this->observers as $observer)
        {
            $observer->update2('comments', $news);
        }
    }
}