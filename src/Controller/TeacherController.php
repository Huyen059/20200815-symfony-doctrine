<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Json;

class TeacherController extends AbstractController
{
    /**
     * @Route("/teachers", name="add_teacher", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function add(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        foreach ($data as $datum) {
            if (empty($datum)) {
                throw new NotFoundHttpException('Missing required input!');
            }
        }

        $address = new Address($data['address']['street'], $data['address']['streetNumber'], $data['address']['city'], $data['address']['zipcode']);
        $teacher = new Teacher();
        $teacher->setFirstName($data['firstName'])
            ->setLastName($data['lastName'])
            ->setEmail($data['email'])
            ->setAddress($address);
        $this->getDoctrine()->getManager()->persist($teacher);
        $this->getDoctrine()->getManager()->flush();
        return new JsonResponse(['status' => 'Teacher added!'], Response::HTTP_OK);
    }

    /**
     * @Route("/teachers/{id}", name="get_one_teacher", methods={"GET"})
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function getOne(Teacher $teacher): JsonResponse
    {
        if ($teacher === null) {
            throw new NotFoundHttpException('Teacher with the requested ID is not found!');
        }

        $data = $teacher->toArray();
        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/teachers", name="get_all_teachers", methods={"GET"})
     * @return JsonResponse
     */
    public function getAll(): JsonResponse
    {
        $teachers = $this->getDoctrine()->getRepository(Teacher::class)->findAll();

        if ($teachers === null) {
            throw new NotFoundHttpException('No teacher found!');
        }

        $data = [];
        foreach ($teachers as $teacher) {
            $data[] = $teacher->toArray();
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    /**
     * @Route("/teachers/{id}", name="update_teacher", methods={"PUT"})
     * @param Teacher $teacher
     * @param Request $request
     * @return JsonResponse
     * @throws \JsonException
     */
    public function update(Teacher $teacher, Request $request): JsonResponse
    {
        if ($teacher === null) {
            throw new NotFoundHttpException('Teacher with the requested ID is not found!');
        }

        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);

        empty($data['firstName']) ? true : $teacher->setFirstName($data['firstName']);
        empty($data['lastName']) ? true : $teacher->setLastName($data['lastName']);
        empty($data['email']) ? true : $teacher->setEmail($data['email']);
        empty($data['address']['street']) ? true : $teacher->getAddress()->setStreet($data['address']['street']);
        empty($data['address']['streetNumber']) ? true : $teacher->getAddress()->setStreetNumber($data['address']['streetNumber']);
        empty($data['address']['city']) ? true : $teacher->getAddress()->setCity($data['address']['city']);
        empty($data['address']['zipcode']) ? true : $teacher->getAddress()->setZipcode($data['address']['zipcode']);

        $this->getDoctrine()->getManager()->persist($teacher);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse($teacher->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/teachers/{id}", name="delete_teacher", methods={"DELETE"})
     * @param Teacher $teacher
     * @return JsonResponse
     */
    public function delete(Teacher $teacher): JsonResponse
    {
        if ($teacher === null) {
            throw new NotFoundHttpException('Teacher with the requested ID is not found!');
        }

        $this->getDoctrine()->getManager()->remove($teacher);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(['status' => 'Teacher deleted!'], Response::HTTP_NO_CONTENT);
    }
}
