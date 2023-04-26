<?php
namespace App\Controller;

use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;


/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiController extends AbstractFOSRestController
{
    // USER URI's

    /**
     * 
     * @Route("/login_check", methods={"POST"}, name="user_login_check")
     * 
     * @OA\Response(
     *     response=200,
     *     description="User was logged in successfully"
     * )
     *
     * @OA\Response(
     *     response=500,
     *     description="User was not logged in successfully"
     * )
     *
     * 
     * @OA\Parameter(
     *      name="_username", in="query", required=true,
     *      description="The username",
     *      @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *      name="_password", in="query", required=true,
     *      description="The password",
     *      @OA\Schema(type="string")
     * )
     *
     *
     * @OA\Tag(name="User")
     */
    public function getLoginCheckAction() { 
    }

    /**
     * 
     * @Route("/register", methods={"POST"}, name="user_register")
     *
     * @OA\Response(
     *     response=201,
     *     description="User was successfully registered",
     * )
     * 
     * @OA\Response(
     *     response=500,
     *     description="User was not successfully registered",
     * )
     * 
     * @OA\Parameter(
     *     name="_name",
     *     in="query",
     *     description="The username",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="_email",
     *     in="query",
     *     description="The username",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="_username",
     *     in="query",
     *     description="The username",
     *     @OA\Schema(type="string")
     * )
     * 
     * @OA\Parameter(
     *     name="_password",
     *     in="query",
     *     description="The username",
     *     @OA\Schema(type="string")
     * )
     * 
     *
     * @OA\Tag(name="User")
     * 
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $encoder) {

        $request = $request->query->all();

        $em = $this->getDoctrine()->getManager();

        $user_created = [];
        $message = "";

        try {
            $code = 200;
            $error = false;

            $name = $request["_name"];
            $email = $request["_email"];
            $username = $request["_username"];
            $password = $request["_password"];

            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($encoder->encodePassword($user, $password));
            $user->setCreatedAt(new \DateTimeImmutable('now'));
            $user->setUpdatedAt(new \DateTimeImmutable('now'));

            $em->persist($user);
            $em->flush();

            $user_created = array(
                'id' => $user->getId(),
                'name' => $user->getName(),
                'username' => $user->getUsername(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
            );
        } catch (Exception $ex) {
            $code = 500;
            $error = true;
            $message = "An error has occurred trying to register the user - Error: {$ex->getMessage()}";
        }

        $response = [
            'code' => $code,
            'error' => $error,
            'data' => $code == 200 ? $user_created : $message,
        ];

        return $this->redirectToRoute('api_auth_login', [
            'username' => $request["_username"],
            'password' => $request["_password"]
        ], 307);
    }

    /**
     * @Route("/v1/get_all_users", name="get_all_users", methods={"GET"})
     */
    public function get_all_users(SerializerInterface $serializer)
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findUsers();
        return new Response($serializer->serialize($users, "json"));
    }

    /**
     * @Route("/v1/", name="api")
     */
    public function api()
    {
        return new Response(sprintf('Logged in as %s', $this->getUser()->getUsername()));
    }
}
