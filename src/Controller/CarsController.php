<?php

namespace App\Controller;

use App\Entity\Car;
use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CarsController extends AbstractController
{
    /**
     * @var CarRepository
     */
    private $carRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        CarRepository $carRepository,
        EntityManagerInterface $entityManager
    )
    {
        $this->carRepository = $carRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAll(Request $request)
    {
        $cars = $this->carRepository->findAll();
        $data = array_map(function (Car $car) {
            return $this->mapEntityResponse($car);
        }, $cars);

        return new JsonResponse($data);
    }

    public function getById($id)
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return $this->responseNotFound();
        }

        return new JsonResponse($this->mapEntityResponse($car));
    }

    public function create(Request $request)
    {
        $params = $this->getRequestParams($request);
        $car = new Car();
        $car->setVendor($params['vendor'])
            ->setModel($params['model'])
            ->setYear($params['year'])
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return new JsonResponse($this->mapEntityResponse($car));
    }

    public function update(Request $request)
    {
        $id = $request->get('id');
        $car = $this->carRepository->find($id);
        if (!$car) {
            return $this->responseNotFound();
        }

        $params = $this->getRequestParams($request);
        $car->setVendor($params['vendor'])
            ->setModel($params['model'])
            ->setYear($params['year'])
            ->setUpdatedAt(new \DateTime());

        $this->entityManager->persist($car);
        $this->entityManager->flush();

        return new JsonResponse($this->mapEntityResponse($car));
    }

    public function delete($id)
    {
        $car = $this->carRepository->find($id);
        if (!$car) {
            return $this->responseNotFound();
        }

        $this->entityManager->remove($car);
        $this->entityManager->flush();

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }

    private function responseNotFound(): JsonResponse
    {
        return new JsonResponse(['error' => 'entity_not_found'], Response::HTTP_NOT_FOUND);
    }

    private function mapEntityResponse(Car $car): array
    {
        return [
            'id' => $car->getId(),
            'vendor' => $car->getVendor(),
            'model' => $car->getModel(),
            'year' => $car->getYear(),
        ];
    }

    /**
     * @param Request $request
     * @return array
     */
    private function getRequestParams(Request $request)
    {
        if ($request->headers->get('Content-Type') !== 'application/json') {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid content type header. Must be application/json');
        }

        $params = json_decode($request->getContent(), true);

        return $params;
    }
}