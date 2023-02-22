<?php

namespace App\Traits;

use Illuminate\Support\Str;


trait ProgramsMall
{
    private $levels = [42, 41, 32, 31, 22, 21, 12];
    private $conPrograms = [
        [
            'id' => 'IT',
            'program' => 'Information Technology',
            'faculty_id' => 'ENG'
        ],
        [
            'id' => 'BC',
            'program' => 'Business Computing',
            'faculty_id' => 'ENG'
        ],
        [
            'id' => 'PRD',
            'program' => 'Production Engineering',
            'faculty_id' => 'ENG'
        ],
        [
            'id' => 'AS',
            'program' => 'Animal Science',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'CS',
            'program' => 'Crop Science',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'FS',
            'program' => 'Food Science',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'BT',
            'program' => 'Bio Tech',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'AGRE',
            'program' => 'Agriculture Economics',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'ENV',
            'program' => 'Environment and Conservation ',
            'faculty_id' => 'AGR'
        ],
        [
            'id' => 'GIS',
            'program' => 'Geography and Information Systems',
            'faculty_id' => 'HUN'
        ],
        [
            'id' => 'DS',
            'program' => 'Development Studies',
            'faculty_id' => 'HUN'
        ],
        [
            'id' => 'MS',
            'program' => 'Media Studies',
            'faculty_id' => 'HUN'
        ],
        [
            'id' => 'SOS',
            'program' => 'Sociology',
            'faculty_id' => 'HUN'
        ],
        [
            'id' => 'LC',
            'program' => 'Language and Communication',
            'faculty_id' => 'HUN'
        ],
        [
            'id' => 'ACC',
            'program' => 'Accounting',
            'faculty_id' => 'COM'
        ],
    ];
    private $blockPrograms = [
        [
            'id' => 'BC',
            'program' => 'Business Computing',
            'faculty_id' => 'ENG'
        ],
        [
            'id' => 'AE',
            'program' => 'Agriculture Economics',
            'faculty_id' => 'AGR'
        ]
    ];


    private $partOneTwo = [];
    private $partTwoOne = [];
    private $partTwoTwo = [];
    private $partThreeOne = [];
    private $partThreeTwo = [];
    private $partFourOne = [];
    private $partFourTwo = [];
    private $partFiveOne = [];
    private $partFiveTwo = [];

    private $partBlockOneTwo = [];
    private $partBlockTwoOne = [];
    private $partBlockTwoTwo = [];
    private $partBlockThreeOne = [];
    private $partBlockThreeTwo = [];
    private $partBlockFourOne = [];
    private $partBlockFourTwo = [];
    private $partBlockFiveOne = [];
    private $partBlockFiveTwo = [];


    public function main()
    {
        // $con = [];
        // $block = [];
        $programs = [];

        foreach ($this->conPrograms as $program) {
            foreach ($this->levels as $level) {

                if ($level === 52 || $level === 51) {
                    if ($program['faculty_id'] === 'ENG' && $program['id'] === 'PRD') {
                        $facade = [
                            'id' => $program['id'] . '' . $level,
                            'program' => $program['program'],
                            'faculty_id' => $program['faculty_id']
                        ];

                        $programs[] = $facade;

                        $this->categorize('con', $level, $facade['id']);
                    }
                } else {
                    $facade = [
                        'id' => $program['id'] . '' . $level,
                        'program' => $program['program'],
                        'faculty_id' => $program['faculty_id']
                    ];

                    $programs[] = $facade;
                    $this->categorize('con', $level, $facade['id']);
                }
            }
        }

        foreach ($this->blockPrograms as $program) {
            foreach ($this->levels as $level) {
                $facade = [
                    'id' => $program['id'] . '' . $level,
                    'program' => $program['program'],
                    'faculty_id' => $program['faculty_id']
                ];

                $programs[] = $facade;

                $this->categorize('block', $level, $facade['id']);
            }
        }


        // return $this->sendData($data);
        return $programs;
    }


    public function levelProgramCodes($studentType, $level)
    {
        switch ($level) {
            case 52:

                if ($studentType === 'con') return $this->partFiveTwo;
                else return $this->partBlockFiveTwo;
            case 51:
                if ($studentType === 'con') return $this->partFiveOne;
                else return $this->partBlockFiveOne;

            case 42:

                if ($studentType === 'con') return $this->partFourTwo;
                else return $this->partBlockFourTwo;

            case 41:
                if ($studentType === 'con') return $this->partFourOne;
                else return $this->partBlockFourOne;

            case 32:
                if ($studentType === 'con') return $this->partThreeTwo;
                else return $this->partBlockThreeTwo;

            case 31:
                if ($studentType === 'con') return $this->partThreeOne;
                else return $this->partBlockThreeOne;

            case 22:
                if ($studentType === 'con') return $this->partTwoTwo;
                else return $this->partBlockTwoTwo;

            case 21:
                if ($studentType === 'con') return $this->partTwoOne;
                else return $this->partBlockTwoOne;

            case 12:
                if ($studentType === 'con') return $this->partOneTwo;
                else return $this->partBlockOneTwo;
        }
    }

    private function categorize($studentType, $level, $levelCode)
    {
        switch ($level) {
            case 52:
                if ($studentType === 'con') {
                    $this->partFiveTwo[] = $levelCode;
                } else {
                    $this->partBlockFiveTwo[] = $levelCode;
                }
                break;
            case 51:
                if ($studentType === 'con') {
                    $this->partFiveTwo[] = $levelCode;
                } else {
                    $this->partBlockFiveTwo[] = $levelCode;
                }
                break;
            case 42:

                if ($studentType === 'con') {
                    $this->partFourTwo[] = $levelCode;
                } else {
                    $this->partBlockFourTwo[] = $levelCode;
                }
                break;
            case 41:
                if ($studentType === 'con') {
                    $this->partFourOne[] = $levelCode;
                } else {
                    $this->partBlockFourOne[] = $levelCode;
                }
                break;
            case 32:
                if ($studentType === 'con') {
                    $this->partThreeTwo[] = $levelCode;
                } else {
                    $this->partBlockThreeTwo[] = $levelCode;
                }
                break;
            case 31:
                if ($studentType === 'con') {
                    $this->partThreeOne[] = $levelCode;
                } else {
                    $this->partBlockThreeOne[] = $levelCode;
                }

                break;
            case 22:

                if ($studentType === 'con') {
                    $this->partTwoTwo[] = $levelCode;
                } else {
                    $this->partBlockTwoTwo[] = $levelCode;
                }

                break;
            case 21:

                if ($studentType === 'con') {
                    $this->partTwoOne[] = $levelCode;
                } else {
                    $this->partBlockTwoOne[] = $levelCode;
                }

                break;
            case 12:

                if ($studentType === 'con') {
                    $this->partOneTwo[] = $levelCode;
                } else {
                    $this->partBlockOneTwo[] = $levelCode;
                }

                break;
        }
    }
}
