<?php

namespace App\Form;

use App\Entity\Shop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class EditShopFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


        for($i=1;$i<=7;$i++)
        $builder->add('o_j_'.$i)
            ->add('f_j_'.$i);
        $builder
            ->add('name')
            ->add('image',FileType::class,array('data_class'=> null, 'label' => 'Image','required' => false))
            ->add('address')
            ->add('city')
            ->add('zip')
            ->add('pays')

            ->add('reduction_montant')
            ->add('reduction_points')

            ->add('spend_rate')
            ->add('reward_rate')
            ->add('threshold')

            ->add('website')
            ->add('email')
            ->add('tel')
            ->add('facebook')
            ->add('twitter')
            ->add('linkdin')
            ->add('instagram')
        ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Shop::class,
        ]);
    }
}