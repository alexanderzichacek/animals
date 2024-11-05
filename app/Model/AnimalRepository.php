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
      $photoUrlsArray = [];
      foreach ($animal->photoUrls->photoUrl as $photoUrl) {
        $photoUrlsArray[] = [
          'photoUrl' => (string) $photoUrl,
        ];
      }
      $tagsArray = [];
      foreach ($animal->tags->tag as $tag) {
        $tagsArray[] = [
          'tag' => $tag,
        ];
      }

      $animals[] = [
        'id' => (string) $animal->id,
        'name' => (string) $animal->name,
        'category' => $animal->category,
        'photoUrls' => (array) $photoUrlsArray,
        'tags' => (array) $tagsArray,
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
        $photoUrlsArray = [];
        foreach ($animal->photoUrls->photoUrl as $photoUrl) {
          $photoUrlsArray[] = [
            'photoUrl' => (string) $photoUrl,
          ];
        }
        $tagsArray = [];
        foreach ($animal->tags->tag as $tag) {
          $tagsArray[] = [
            'tag' => $tag,
          ];
        }

        $animalById[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => (array) $photoUrlsArray,
          'tags' => (array) $tagsArray,
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
      $photoUrlsArray = [];
      foreach ($animal->photoUrls->photoUrl as $photoUrl) {
        $photoUrlsArray[] = [
          'photoUrl' => (string) $photoUrl,
        ];
      }
      $tagsArray = [];
      $tagsNames = [];
      foreach ($animal->tags->tag as $singleTag) {
        $tagsArray[] = [
          'tag' => $singleTag,
        ];
        $tagsNames[] = $singleTag->name;
      }

      if (in_array($tag, $tagsNames)) {
        $animalsByTag[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => $photoUrlsArray,
          'tags' => $tagsArray,
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
        $photoUrlsArray = [];
        foreach ($animal->photoUrls->photoUrl as $photoUrl) {
          $photoUrlsArray[] = [
            'photoUrl' => (string) $photoUrl,
          ];
        }
        $tagsArray = [];
        foreach ($animal->tags->tag as $tag) {
          $tagsArray[] = [
            'tag' => $tag,
          ];
        }

        $animalsByStatus[] = [
          'id' => (string) $animal->id,
          'name' => (string) $animal->name,
          'category' => $animal->category,
          'photoUrls' => (array) $photoUrlsArray,
          'tags' => (array) $tagsArray,
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

  public function deleteAnimal($id): array
  {
    $xml = $this->controlFile();

    $animal = $xml->xpath("//animal[id='{$id}']");

    if (empty($animal)) {
      return ['message' => 'Animal not found', 'status' => 404];
    }

    $animal = $animal[0];

    $dom = dom_import_simplexml($animal);
    $dom->parentNode->removeChild($dom);

    $xml->asXML($this->xmlFilePath);

    return ['message' => 'Animal deleted successfully', 'status' => 200];
  }

  public function updateAnimal($id, $data): array
  {
    $xml = $this->controlFile();

    if (empty($id)) {
      return ['message' => 'Id of pet is required', 'status' => 400];
    }

    $animal = $xml->xpath("//animal[id='{$id}']");

    if (empty($animal)) {
      return ['message' => 'Animal not found', 'status' => 404];
    }

    $animal = $animal[0];

    if (isset($data['name'])) {
      $animal->name = htmlspecialchars($data['name']);
    }

    if (isset($data['category'])) {
      $category = $animal->category;
      $category->id = htmlspecialchars($data['category']['id']);
      $category->name = htmlspecialchars($data['category']['name']);
    }

    if (isset($data['photoUrls'])) {
      unset($animal->photoUrls);
      $photoUrls = $animal->addChild('photoUrls');
      foreach ($data['photoUrls'] as $photoUrl) {
        $photoUrls->addChild('photoUrl', htmlspecialchars($photoUrl['photoUrl']));
      }
    }

    if (isset($data['tags'])) {
      unset($animal->tags);
      $tags = $animal->addChild('tags');
      foreach ($data['tags'] as $tag) {
        $tagNode = $tags->addChild('tag');
        $tagNode->addChild('id', htmlspecialchars($tag['id']));
        $tagNode->addChild('name', htmlspecialchars($tag['name']));
      }
    }

    if (isset($data['status'])) {
      $animal->status = htmlspecialchars($data['status']);
    }

    $xml->asXML($this->xmlFilePath);

    return ['message' => 'Animal updated successfully', 'status' => 200];
  }
}
