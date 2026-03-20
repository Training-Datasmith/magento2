<?php

declare (strict_types=1);
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Console\Question_Performer;

use Magento\Framework\Exception\Localized_Exception;
use Magento\Framework\Phrase;
use Symfony\Component\Console\Helper\Question_Helper;
use Symfony\Component\Console\Input\Input_Interface;
use Symfony\Component\Console\Output\Output_Interface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\Question_Factory;
/**
 * Asks a questions to the user.
 */
class Yes_No
{
    /**
     * Provides helpers to interact with the user.
     *
     * @var QuestionHelper
     */
    private $question_helper;
    /**
     * The factory for creating Question objects.
     *
     * @var QuestionFactory
     */
    private $question_factory;
    /**
     * @param QuestionHelper $questionHelper Provides helpers to interact with the user
     * @param QuestionFactory $questionFactory The factory for creating Question objects
     */
    public function __construct(Question_Helper $question_helper, Question_Factory $question_factory)
    {
        $this->question_helper = $question_helper;
        $this->question_factory = $question_factory;
    }
    /**
     * Asks a question to the user. The question is generates from given array of messages.
     *
     * @param string[] $messages The array of messages for creating a question
     * @param InputInterface $input An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     * @return bool
     */
    public function execute(array $messages, Input_Interface $input, Output_Interface $output): bool
    {
        if (!$input->is_interactive()) {
            return true;
        }
        $question = $this->get_confirmation_question($messages);
        $answer = $this->question_helper->ask($input, $output, $question);
        return in_array(strtolower($answer ?? ''), ['yes', 'y']);
    }
    /**
     * Creates Question object from given array of messages.
     *
     * @param string[] $messages array of messages
     * @return Question
     * @throws LocalizedException is thrown when a user entered a wrong answer
     */
    private function get_confirmation_question(array $messages)
    {
        /** @var Question $question */
        $question = $this->question_factory->create(['question' => implode(PHP_EOL, $messages) . PHP_EOL]);
        $question->set_validator(function ($answer) {
            if (!in_array(strtolower($answer ?? ''), ['yes', 'y', 'no', 'n'])) {
                throw new Localized_Exception(new Phrase('A [y]es or [n]o selection needs to be made. Select and try again.'));
            }
            return $answer;
        });
        return $question;
    }
}