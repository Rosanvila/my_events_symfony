<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use App\Form\EventType;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsLiveComponent('event_form')]
final class CreateEvent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;
    use ComponentToolsTrait;

    private Event $event;

    #[LiveProp()]
    public ?Event $initialFormData = null;

    #[LiveProp()]
    public string $base64Photo = '';

    #[LiveProp]
    public string $photoUploadError = '';


    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {}

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EventType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function updatePicturePreview(Request $request)
    {
        $this->photoUploadError = '';
        $file = $request->files->get('event')['photo'];
        if ($file instanceof UploadedFile) {
            $this->validateSingleFile($file);
            $this->base64Photo = base64_encode(file_get_contents($file->getPathname()));
            $this->dispatchBrowserEvent('picture:changed', ["base64" => $this->base64Photo]);
        }
    }

    private function validateSingleFile(UploadedFile $singleFileUpload): void
    {
        $errors = $this->validator->validate($singleFileUpload, [
            new Image([
                'maxSize' => '5M',
                'mimeTypes' => [
                    'image/png',
                    'image/jpeg',
                ],
            ]),
        ]);

        if (0 === \count($errors)) {
            return;
        }

        $this->photoUploadError = $errors->get(0)->getMessage();
        $this->dispatchBrowserEvent('picture:changed', ["base64" => ""]);

        // causes the component to re-render
        throw new UnprocessableEntityHttpException('Validation failed');
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        $form = $this->getForm();
        $this->submitForm();

        if (!$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs.');
            return;
        }

        $this->event = $form->getData();

        // photo 
        if (!is_null($this->base64Photo) && !empty($this->base64Photo)) {
            $this->event->setPhoto($this->base64Photo);
        }

        if (empty($this->event->getOrganizer())) {
            $this->event->setOrganizer($this->getUser());
        }

        if (empty($this->event->getCreatedAt())) {
            $this->event->setCreatedAt(new \DateTimeImmutable());
        }

        $entityManager->persist($this->event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event_index');
    }
}
