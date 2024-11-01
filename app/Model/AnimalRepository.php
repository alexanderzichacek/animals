<?php
namespace App\Model;

use SimpleXMLElement;
use Nette\Application\Responses\JsonResponse;

class AnimalRepository
{
  private $xmlFilePath;

  public function __construct()
  {
    $this->xmlFilePath = __DIR__ . '/../../data/animals.xml';
  }

  public function controlFile()
  {
    if (!file_exists($this->xmlFilePath)) {
      throw new \Exception('File ' . $this->xmlFilePath . ' not found');
    }

    $xml = simplexml_load_file($this->xmlFilePath);

    if ($xml === false) {
      throw new \Exception('File ' . $this->xmlFilePath . ' failed to parse');
    }

    return $xml;
  }

  public function findAll(): array
  {
    $xml = $this->controlFile();

    $animals = [];
    foreach ($xml->animal as $animal) {
      $animals[] = [
        'id' => (string) $animal->id,
        'name' => (string) $animal->name,
        'category' => $animal->category,
        'photoUrls' => $animal->photoUrls,
        'tags' => $animal->tags,
        'status' => (string) $animal->status,
      ];
    }

    return $animals;
  }

  public function findById($id): array
  {
    $xml = $this->controlFile();

    $animalById = [];
    foreach ($xml->animal as $animal) {
      if ($animal->id == $id) {
        $animalById[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => $animal->photoUrls,
          'tags' => $animal->tags,
          'status' => (string) $animal->status,
        ];
      }
    }

    return $animalById;
  }

  public function findByTag($tag): array
  {
    $xml = $this->controlFile();

    $animalsByTag = [];
    foreach ($xml->animal as $animal) {
      if ($animal->tags->tag->name == $tag) {
        $animalsByTag[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => $animal->photoUrls,
          'tags' => $animal->tags,
          'status' => (string) $animal->status,
        ];
      }
    }

    return $animalsByTag;
  }

  public function findByStatus($status): array
  {
    $xml = $this->controlFile();

    $animalsByStatus = [];
    foreach ($xml->animal as $animal) {
      if ($animal->status == $status) {
        $animalsByStatus[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => $animal->photoUrls,
          'tags' => $animal->tags,
          'status' => (string) $animal->status,
        ];
      }
    }

    return $animalsByStatus;
  }

  public function createAnimal($data): array
  {
    $xml = $this->controlFile();

    if (!isset($data['id'], $data['name'], $data['category'], $data['status'], $data['photoUrls'], $data['tags'])) {
      return ['message' => 'Missing required fields', 'status' => 400];
    }

    $newAnimal = $xml->addChild('animal');
    $newAnimal->addChild('id', htmlspecialchars($data['id']));
    $newAnimal->addChild('name', htmlspecialchars($data['name']));

    $category = $newAnimal->addChild('category');
    $category->addChild('id', htmlspecialchars($data['category']['id']));
    $category->addChild('name', htmlspecialchars($data['category']['name']));

    if (is_array($data['photoUrls'])) {
      $photoUrls = $newAnimal->addChild('photoUrls');
      foreach ($data['photoUrls'] as $photoUrl) {
        $photoUrls->addChild('photoUrl', htmlspecialchars($photoUrl['photoUrl']));
      }
    } else
      return ['message' => 'Missing photoUrls', 'status' => 400];

    if (is_array($data['tags'])) {
      $tags = $newAnimal->addChild('tags');
      foreach ($data['tags'] as $tag) {
        $tagNode = $tags->addChild('tag');
        $tagNode->addChild('id', htmlspecialchars($tag['id']));
        $tagNode->addChild('name', htmlspecialchars($tag['name']));
      }
    } else
      return ['message' => 'Missing tags', 'status' => 400];

    $newAnimal->addChild('status', htmlspecialchars($data['status']));

    $xml->asXML($this->xmlFilePath);

    return ['message' => 'Animal added successfully', 'status' => 201];
  }
}
