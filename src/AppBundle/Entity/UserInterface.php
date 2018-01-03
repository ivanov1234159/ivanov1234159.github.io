<?php

namespace AppBundle\Entity;


interface UserInterface
{
    public function setId(int $id);
    public function getId(): int;

    public function setNickname(string $nickname);
    public function getNickname();

    public function setUsername(string $username);
    public function getUsername();

    public function setPassword(string $password);
    public function getPassword();

    public function setEmail(string $email);
    public function getEmail();

    public function setKingdomId(int $kingdomId);
    public function getKingdomId(): int;

    public function setDateCreated(\DateTime $dateCreated);
    public function getDateCreated(): \DateTime;
}