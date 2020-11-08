<?php
namespace WPGen\Commands\Traits;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

trait QueryOptions
{
    public function querySecondaryOptions( InputInterface $input, OutputInterface $output, &$options ) {
        $this->queryOptions( $input, $output, $options );
        $this->confirmOptions( $input, $output, $options );
        $this->mergeOptions( $options );
    }

    public function mergeOptions( $options ) {
        // Merge module options into main options.
        foreach( $options as $option ) {
            if ( ! isset( $option['value'] ) ) {
                $option['value'] = '';
            }
            $this->options[$option['key']] = $option;
        }
    }

    /**
     * Query each option.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @poram array $options
     */
    protected function queryOptions( InputInterface $input, OutputInterface $output, &$options ) {
        foreach ( $options as &$option ) {
            $option['value'] = $this->queryOption( $input, $output, $option );
        }
    }

    /**
     * Set an option value.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $option
     * @return mixed
     */
    protected function queryOption( InputInterface $input, OutputInterface $output, $option ) {

        if ( ! isset( $option['value'] ) ) {
            $option['value'] = '';
        }

        $label = $option['label'];
        $description = $option['description'];
        $label_text = str_pad( $label, 60 );
        $helper = $this->getHelper( 'question' );
        $question_text = sprintf( '<question> %s </question> ', $label_text );
        $description_text = sprintf( '<comment> %s</comment>', $description );
        $current_value = sprintf( 'Current Value: <info> %s</info>', $option['value'] );

        // Output lines.
        $lines = [$question_text, $description_text];

        // Show current value if defined.
        if ( !empty( $option['value'] ) ) {
            $lines[] = $current_value;
        }

        $output->writeln( $lines );

        $skip = false;
        if ( isset( $option['if'] ) ) {
            foreach( $option['if'] as $key => $value ) {
                if ( $this->options[$key]['value'] != $value ) {
                    $skip = true;
                }
            }
        }

        if ( $skip ) {
            return '';
        }

        // Toggle question renderer.
        switch ( $option['type'] )
        {
            // Choice select.
            case 'select':
                // Prompt which question to change.
                $helper = $this->getHelper( 'question' );
                $values = [];
                foreach ( $option['options'] as $data ) {
                    $values[] = $data['label'];
                }
                $question = new ChoiceQuestion( 'Select:', $values, 0 );
                return $helper->ask( $input, $output, $question );
                break;

            // True/false questions
            case 'boolean':
                // Prompt user if anything items are wrong.
                $helper = $this->getHelper( 'question' );
                $question = new ConfirmationQuestion( 'Is everything correct? (y/n)', false );
                return $helper->ask( $input, $output, $question );
                break;

            // Handle string type options (Default).
            case 'string':
            default:
                // Prompt new value.
                $question = new Question( '> ' );
                $question->setValidator( function( $value ) {
                    return $this->validateValue( $value );
                } );
                return $helper->ask( $input, $output, $question );
                break;
        }
    }

    /**
     * Confirm options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $options
     * @return bool
     */
    protected function confirmOptions( InputInterface $input, OutputInterface $output, &$options ) {

        // Show current value selection.
        $this->showSelectedOptionValues( $input, $output, $options );

        // Prompt user if anything items are wrong.
        $helper = $this->getHelper( 'question' );
        $question = new ConfirmationQuestion( 'Is everything correct? (y/n)', false );

        // If answers are deemed correct, return function to move on to next logic.
        if ( $helper->ask( $input, $output, $question ) ) {
            return true;
        }

        // Prompt which question to change.
        $helper = $this->getHelper( 'question' );
        $values = [];
        foreach ( $options as $data ) {
            $values[] = $data['label'];
        }
        $question = new ChoiceQuestion( 'Select item to change', $values, 0 );

        $label = $helper->ask( $input, $output, $question );

        foreach ( $options as &$option ) {
            if ( $option['label'] === $label ) {
                $option['value'] = $this->queryOption( $input, $output, $option );
            }
        }

        return $this->confirmOptions( $input, $output, $options );
    }

    /**
     * Print a table of current options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $options
     */
    protected function showSelectedOptionValues( InputInterface $input, OutputInterface $output, $options ) {

        // Prepare table values array.
        $rows = [];
        foreach ( $options as $option ) {
            $rows[] = [
                $option['label'],
                $option['value']
            ];
        }

        // Print table.
        $table = new Table( $output );
        $table->setHeaders( ['Option', 'Value'] )->setRows( $rows );
        $table->render();
    }

    /**
     * Validate user input.
     *
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    protected function validateValue( $value ) {

        if ( trim( $value ) == '' ) {
            throw new \Exception( 'A value is required.' );
        }

        // Problematic strings and characters...
        $replacements = [
            '"',
            '/*',
            '*/'
        ];

        foreach ( $replacements as $char ) {
            $value = str_replace( $char, '', $value );
        }

        return $value;
    }
}