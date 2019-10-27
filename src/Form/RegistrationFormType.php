<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;

class RegistrationFormType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('tel', TextType::class, [])
			->add('email', EmailType::class, [])
			->add('lastname', TextType::class, [])
			->add('firstname', TextType::class, [])
			->add('zip', TextType::class, [])
			->add('city', TextType::class, [])
			->add('plainPassword', RepeatedType::class, [
				// instead of being set onto the object directly,
				// this is read and encoded in the controller
				'type' => PasswordType::class,
				'invalid_message' => 'La confirmation du mot de passe doit correspondre.',
				'required' => true,
				'mapped' => false,
				'first_options'  => ['label' => 'Mot de passe'],
				'second_options' => ['label' => 'Confirmation du mot de passe'],
				'constraints' => [
					new NotBlank([
						'message' => 'Veuillez renseigner un mot de passe',
					]),
					new Length([
						'min' => 6,
						'minMessage' => 'Votre mot de passe doit comporter au moins {{ limit }} caractères',
						'max' => 4096,
					]),
				],
			])
		;
	}

	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
