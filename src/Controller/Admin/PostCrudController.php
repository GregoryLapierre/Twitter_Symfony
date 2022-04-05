<?php

namespace App\Controller\Admin;

use App\Entity\Post;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PostCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Post::class;
    }

    public function createEntity(string $entityFqcn){
        $post = new Post();
        $post->setUser($this->getUser());
        return $post;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('title'),
            TextareaField::new('content'),
            ChoiceField::new('status')->setChoices([
                'Ouvert' => 'opened',
                'Modéré' => 'moderated',
                'Fermé' => 'closed'
            ]),
            DateTimeField::new('created_at')
        ];
    }
    
}
