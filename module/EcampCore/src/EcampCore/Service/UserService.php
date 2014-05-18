<?php

namespace EcampCore\Service;

use EcampCore\Acl\Privilege;
use EcampCore\Entity\User;
use EcampCore\Entity\Image;
use EcampCore\Fieldset\User\UserCreateFieldset;
use EcampCore\Repository\UserRepository;

use EcampLib\Service\ServiceBase;
use EcampLib\Service\Params\Params;

use Zend\Paginator\Paginator;

class UserService
    extends ServiceBase
{

    /**
     * @var \EcampCore\Repository\UserRepository
     */
    private $userRepo;

    public function __construct(
        UserRepository $userRepo
    ){
        $this->userRepo = $userRepo;
    }

    /**
     * Returns the User with the given Identifier
     * (Identifier can be a MailAddress, a Username or a ID)
     *
     * If no Identifier is given, the Authenticated User is returned
     *
     * @return User
     */
    public function Get($id = null)
    {
        if (isset($id)) {
            $user = $this->getByIdentifier($id);
        } else {
            $user = $this->getMe();
        }

        return $this->aclIsAllowed($user, Privilege::USER_LIST)
            ? $user
            : null;
    }

    /**
     * Creates a new User with $username
     *
     * @param $userInput
     * @return User
     */
    public function Create($userInput)
    {
        $inputFilter = UserCreateFieldset::createInputFilterSpecification();
        $filteredUserInput = $this->validateInputArray($userInput, $inputFilter);

        $email = $filteredUserInput['mail'];

        /** @var $user User */
        $user = $this->userRepo->findOneBy(array('email' => $email));

        if (is_null($user)) {
            $user = new User();
            $user->setEmail($email);

            $this->persist($user);
        }

        $hydrator = new \Zend\Stdlib\Hydrator\ClassMethods();
        $hydrator->hydrate($filteredUserInput, $user);

        return $user;
    }

    public function Update(User $user, Params $params)
    {
        $userValidator = new \Core\Validator\Entity\UserValidator($user);

        $this->validationFailed(
            ! $userValidator->applyIfValid($params));

        return $user;
    }

    public function Delete(User $user)
    {
        // delete user
        $this->remove($user);
    }

    public function SetImage(User $user, $data, $mime)
    {
        $image = new Image();
        $image->setData($data);
        $image->setMime($mime);

        $user->setImage($image);
    }

    public function DeleteImage(User $user)
    {
        $user->setImage(null);
    }

    /**
     * Returns the User for a MailAddress or a Username
     *
     * @param  string $identifier
     * @return User
     */
    private function getByIdentifier($identifier)
    {
        if ($identifier instanceof User) {
            return $identifier;
        } else {
            return $this->userRepo->findByIdentifier($identifier);
        }
    }

    /**
     * Get all users and wrap in paginator
     * @return \Zend\Paginator\Paginator
     */
    public function GetPaginator()
    {
        $query = $this->userRepo->createQueryBuilder("u");
        $adapter = new \EcampCore\Paginator\Doctrine($query);

        return new Paginator($adapter);
    }
}
