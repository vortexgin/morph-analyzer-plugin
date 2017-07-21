<?php

namespace Vortexgin\MorphAnalyzerBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MorphManager{

    private $container;

    private $morphind;

    public function __construct(ContainerInterface $container){
        $this->container = $container;
        $basePath = realpath(dirname(__FILE__)).'/../../../../';
        $this->morphind = $basePath.'bin/morphind.v.1.4/MorphInd.pl';
    }

    public function analyze($word){
        try{
          $command = 'echo "'.$word.'" | perl '.$this->morphind;
          $process = new Process($command);
          $process->run();

          if (!$process->isSuccessful()) {
              return false;
          }

          $output = $process->getOutput();
          $return = $this->extract($output);

          return array(
              'word' => $word,
              'morph' => $return,
          );
        }catch(\Exception $e){
            return false;
        }
    }

    public function extract($morph){
        try{
            $regexp = '\^(.*?)\$';
            if(!preg_match_all("/$regexp/si", $morph, $matches)){
                return false;
            }

            $structure = $matches[1];
            $morph = $this->morph($structure);

            return $morph;
        }catch(\Exception $e){
            return false;
        }
    }

    private $lemmaTag = [
      'n' => 'Noun', //kata benda
      'p' => 'Personal Pronoun',
      'v' => 'Verb', //kata kerja
      'c' => 'Numeral', //kata bilangan
      'q' => 'Adjective', //kata sifat
      'h' => 'Coordinating Conjunction', //kata hubung yang menghubungkan dua klausa yang setara (dan, jadi)
      's' => 'Subordinating Conjunction',
      'f' => 'Foreign Word',
      'r' => 'Preposition',
      'm' => 'Modal',
      'b' => 'Determiner',
      'd' => 'Adverb', //kata keterangan
      't' => 'Particle', //imbuhan
      'g' => 'Negation',
      'i' => 'Interjection',
      'o' => 'Copula',
      'w' => 'Question',
      'x' => 'Unknown',
      'z' => 'Punctuation',
    ];
    private $morph = [
        1 => [
            'N' => 'Noun',
            'P' => 'Personal Pronoun',
            'V' => 'Verb',
            'C' => 'Numeral',
            'Q' => 'Adjective',
            'H' => 'Coordinating Conjunction',
            'S' => 'Subordinating Conjunction',
            'F' => 'Foreign Word',
            'R' => 'Preposition',
            'M' => 'Modal',
            'B' => 'Determiner',
            'D' => 'Adverb',
            'T' => 'Particle',
            'G' => 'Negation',
            'I' => 'Interjection',
            'O' => 'Copula',
            'W' => 'Question',
            'X' => 'Unknown',
            'Z' => 'Punctuation',
        ],
        2 => [
            'N' => [
                'P' => 'Plural',
                'S' => 'Singular',
            ],
            'P' => [
                'P' => 'Plural',
                'S' => 'Singular',
            ],
            'V' => [
                'P' => 'Plural',
                'S' => 'Singular',
            ],
            'C' => [
                'C' => 'Cardinal Numeral',
                'O' => 'Ordinal Numeral',
                'D' => 'Collective Numeral',
            ],
            'A' => [
                'P' => 'Plural',
                'S' => 'Singular',
            ],
        ],
        3 => [
            'N' => [
                'F' => 'Feminine',
                'M' => 'Masculine',
                'D' => 'Non-Specified',
            ],
            'P' => [
                '1' => 'First Person',
                '2' => 'Second Person',
                '3' => 'Third Person',
            ],
            'V' => [
                'A' => 'Active Voice',
                'P' => 'Passive Voice',
            ],
            'A' => [
                'P' => 'Positive',
                'S' => 'Superllative',
            ],
        ],
    ];
    private function morph($structure){
        try{
            $return = array();
            foreach($structure as $key=>$morphWord){
                $regexp = '(.*)<(.*)>_(.*)';
                $word = null;
                if(preg_match_all("/$regexp/si", $morphWord, $matches)){
                    $word = $matches[1][0];
                    $lemma = $this->lemmaTag[$matches[2][0]];
                    $morphTag = $matches[3][0];
                    $morph = array();
                    if($morphTag[0] != '-'){
                        $morph[0] = $this->morph[1][$morphTag[0]];
                    }
                    if($morphTag[1] != '-'){
                        $morph[1] = $this->morph[2][$morphTag[0]][$morphTag[1]];
                    }
                    if($morphTag[2] != '-'){
                        $morph[2] = $this->morph[3][$morphTag[0]][$morphTag[2]];
                    }

                    $return[] = array(
                        'word' => $word,
                        'lemma' => $lemma,
                        'morph' => $morph,
                    );
                }
            }

            return $return;
        }catch(\Exception $e){
            return false;
        }
    }

    private function unknown($structure){
        try{
        }catch(\Exception $e){
            return false;
        }
    }
}
