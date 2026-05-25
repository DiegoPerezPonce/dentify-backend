<?php



namespace App\Dto;



use Symfony\Component\Validator\Constraints as Assert;



class WeeklyScheduleDayInputDTO

{

    #[Assert\Range(min: 1, max: 6)]

    public int $weekday = 1;



    #[Assert\NotBlank]

    public string $shift = 'off';

}

