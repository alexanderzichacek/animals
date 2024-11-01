<?php
namespace App\UI\Api;

use Nette\Application\Responses\JsonResponse;
use Nette\Application\UI\Presenter;
use App\Model\AnimalRepository;

class ApiPresenter extends Presenter
{
    private $animalRepository;

    public function __construct()
    {
        $this->animalRepository = new AnimalRepository();
    }

    public function actionGetById(int $id): void
    {
        if ($this->getHttpRequest()->getMethod() !== 'GET') {
            $this->error('Method Not Allowed', 405);
        }
        $animalById = $this->animalRepository->findById($id);

        $this->sendResponse(new JsonResponse($animalById));
    }

    public function actionGetByTag(string $tag): void
    {
        if ($this->getHttpRequest()->getMethod() !== 'GET') {
            $this->error('Method Not Allowed', 405);
        }
        $animalsByTag = $this->animalRepository->findByTag($tag);

        $this->sendResponse(new JsonResponse($animalsByTag));
    }

    public function actionGetByStatus(string $status): void
    {
        if ($this->getHttpRequest()->getMethod() !== 'GET') {
            $this->error('Method Not Allowed', 405);
        }
        $animalsByStatus = $this->animalRepository->findByStatus($status);

        $this->sendResponse(new JsonResponse($animalsByStatus));
    }

    public function actionProcessPetRequest(): void
    {
        if ($this->getHttpRequest()->getMethod() === 'GET') {
            $animals = $this->animalRepository->findAll();

            $this->sendResponse(new JsonResponse($animals));
        } elseif ($this->getHttpRequest()->getMethod() === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);

            $result = $this->animalRepository->createAnimal($data);

            $this->sendResponse(new JsonResponse($result['message'], $result['status']));
        }
    }

}