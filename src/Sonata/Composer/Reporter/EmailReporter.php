<?php

/*
* This file is part of the Sonata project.
*
* (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Sonata\Composer\Reporter;

class EmailReporter implements ReporterInterface
{
    /**
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Message $message)
    {
        $transport = \Swift_SmtpTransport::newInstance($this->settings['host'], $this->settings['port']);
        if ($this->settings['username']) {
            $transport->setUsername($this->settings['username']);
        }

        if ($this->settings['password']) {
            $transport->setUsername($this->settings['password']);
        }

        $mailer = \Swift_Mailer::newInstance($transport);

        $mail = new \Swift_Message(
            sprintf($this->settings['subject'], $message->getProject(), $message->getStatus()),
            sprintf(
"Sonata Composer Archiver
~~~~~~~~~~~~~~~~~~~~~~~~~

%s

", $message->getContent())
        );

        $mail->setTo($this->settings['to']);
        $mail->setFrom($this->settings['from']);

        $mailer->send($mail);
    }
}