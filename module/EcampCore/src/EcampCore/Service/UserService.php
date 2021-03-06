<?php

namespace EcampCore\Service;

use EcampCore\Acl\Privilege;
use EcampCore\Entity\User;
use EcampCore\Entity\Image;
use EcampCore\Repository\UserRepository;
use EcampCore\Validator\User\UniqueMailAddress;
use EcampLib\Validation\ValidationException;

class UserService extends Base\ServiceBase
{

    /**
     * @var \EcampCore\Repository\UserRepository
     */
    private $userRepo;

    /**
     * @var ResqueJobService
     */
    private $resqueJobService;

    public function __construct(
        UserRepository $userRepo,
        $resqueJobService
    ){
        $this->userRepo = $userRepo;
        $this->resqueJobService = $resqueJobService;
    }

    /**
     * Returns the User with the given Identifier
     * (Identifier can be a MailAddress, a Username or a ID)
     *
     * If no Identifier is given, the Authenticated User is returned
     *
     * @param  null $id
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
     * @param $data
     * @throws \EcampLib\Validation\ValidationException
     * @return User
     */
    public function Create($data)
    {
        $email = $data['email'];

        $mailValidator = new \Zend\Validator\EmailAddress();
        if (! $mailValidator->isValid($email)) {
            throw new ValidationException(array('email' => $mailValidator->getMessages()));
        }

        /** @var $user User */
        $user = $this->userRepo->findOneBy(array('email' => $email));

        if (is_null($user)) {
            $user = new User();

            $validationForm = $this->createValidationForm($user, $data, array('firstname', 'surname', 'scoutname', 'username', 'email'));
            if ($validationForm->isValid()) {
                $this->persist($user);
            } else {
                throw ValidationException::FromForm($validationForm);
            }

        } else {
            $validationForm = $this->createValidationForm($user, $data, array('firstname', 'surname', 'scoutname', 'username'));
            if (!$validationForm->isValid()) {
                throw ValidationException::FromForm($validationForm);
            }
        }

        return $user;
    }

    public function Update($userId, $data)
    {
        $user = $this->Get($userId);
        $this->aclRequire($user, Privilege::USER_ADMINISTRATE);

        $userValidationForm = $this->createValidationForm(
            $user,
            $data,
            array_intersect(
                array_keys($data),
                array(
                    'scoutname',
                    'firstname',
                    'surname',
                    'street',
                    'zipcode',
                    'city',
                    'homeNr',
                    'mobilNr',
                    'gender',
                    'birthday',
                    'ahv',
                    'jsPersNr',
                    'jsEdu',
                    'pbsEdu'
                )
            )
        );

        if (!$userValidationForm->isValid()) {
            throw ValidationException::FromForm($userValidationForm);
        }

        return $user;
    }

    public function UpdateEmail($userId, $email)
    {
        $user = $this->Get($userId);
        $this->aclRequire($user, Privilege::USER_ADMINISTRATE);

        $mailValidator = new \Zend\Validator\EmailAddress();
        if (! $mailValidator->isValid($email)) {
            throw new ValidationException(array('email' => $mailValidator->getMessages()));
        }

        $uniqueMail = new UniqueMailAddress($this->getEntityManager());
        if (!$uniqueMail->isValid($email)) {
            throw new ValidationException(array('email' => $uniqueMail->getMessages()));
        }

        $user->setUntrustedEmail($email);

        $this->resqueJobService->Create(
            'SendEmailVerificationEmail',
            array('userId' => $user->getId()),
            true
        );
    }

    public function VerifyEmail($userId, $verificationCode)
    {
        $user = $this->Get($userId);

        if (!$user->verifyEmail($verificationCode)) {
            throw ValidationException::Create(array('verificationCode' => array('Email verification failed')));
        }
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

}
