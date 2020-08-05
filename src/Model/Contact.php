<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class Contact
{
    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Votre prénom doit avoir plus de deux caractères",
     * )
     * @Assert\Type("string")
     * @Assert\NotBlank(message ="Le champ doit être obligatoirement rempli")
     */
    private $firstname;

    /**
     * @Assert\Length(
     *      min = 2,
     *      max = 100,
     *      minMessage = "Votre nom doit avoir plus de deux caractères",
     * )
     * @Assert\Type("string")
     * @Assert\NotBlank(message ="Le champ doit être obligatoirement rempli")
     */
    private $lastname;

    /**
     * @Assert\Type("string")
     * @Assert\Email(
     *      message = "L'adresse mail n'est une adresse valide")
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 100,
     *      minMessage = "Votre sujet doit avoir plus de un caractère",
     * )
     * @Assert\Type("string")
     * @Assert\NotBlank(message = "Le champ doit être obligatoirement rempli")
     */
    private $subject;

    /**
     * @Assert\Length(
     *      min = 1,
     *      max = 1500,
     *      maxMessage = "Le champ ne doit pas dépasser 1500 caractères")
     * @Assert\NotBlank(message = "Le champ doit obligatoirement être rempli")
     */
    private $description;
    /**
     * @return string
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }
    /**
     * @param string $firstname
     * @return Contact
     */
    public function setFirstname(string $firstname): Contact
    {
        $this->firstname = $firstname;
        return $this;
    }
    /**
     * @return string
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }
    /**
     * @param string $lastname
     * @return Contact
     */
    public function setLastname(string $lastname): Contact
    {
        $this->lastname = $lastname;
        return $this;
    }
    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }
    /**
     * @param string $email
     * @return Contact
     */
    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }
    /**
     * @return string
     */
    public function getSubject(): ?string
    {
        return $this->subject;
    }
    /**
     * @param string $subject
     * @return Contact
     */
    public function setSubject(string $subject): Contact
    {
        $this->subject = $subject;
        return $this;
    }
    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }
    /**
     * @param string $description
     * @return Contact
     */
    public function setDescription(string $description): Contact
    {
        $this->description = $description;
        return $this;
    }
}
