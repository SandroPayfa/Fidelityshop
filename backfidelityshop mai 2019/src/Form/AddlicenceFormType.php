<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Shop;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class AddlicenceFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $shops)
	{

		$builder
			->add('pin', TextType::class, [])
			->add('magasin', TextType::class, [])
//			->add('terminal_id', TextType::class, [])
            ->add('periode',  ChoiceType::class, [
                'choices'  => [
                    '3 Mois' => '3',
                    '6 Mois' => '6',
                    '12 Mois' => '12',
                ],
            ])
            ->add('magasin',  EntityType::class, [
                'class' => Shop::class,
                // this method must return an array of User entities
                'choice_label' => function ($category) {
                    return $category->getName();
                }
            ]);
	}
/*
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}*/
}
