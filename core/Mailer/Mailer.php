<?php

namespace Core\Mailer;

use Error;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer {
    const AVAILABLE_SENDER_TYPES = ['smtp'];
    const TEMPLATE_MAIL_PATH = __DIR__ . '/Templates';

    private ?PHPMailer $mailer;

    public function __construct()
    {
        $this->mailer = new PHPMailer();

        $this->configure($_ENV['MAIL_MAILER'], $_ENV['MAIL_SECURE_PROTOCOL']);
    }
    
    /**
     *  Configure the class parameters needed
     *
     * @param  string $type Type of mailer to use, only supported by this class, check  `AVAILABLE_SENDER_TYPES` const.
     * @param  string $smtpSecure Protocol to use in the smtp connection.
     * @return void
     */
    private function configure(string $type, string $smtpSecure){
        $TYPES = join(', ',Mailer::AVAILABLE_SENDER_TYPES);

        switch ($type) {
            case 'smtp':
                $this->mailer->isSMTP();
                break;
            
            default:
                throw new Error("$type not supported yet, supported types: $TYPES");
                break;
        }

        $this->mailer->Host = $_ENV['MAIL_HOST'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $_ENV['MAIL_USERNAME'];
        $this->mailer->Password = $_ENV['MAIL_PASSWORD'];
        $this->mailer->SMTPSecure = $smtpSecure;
        $this->mailer->Port = $_ENV['MAIL_PORT'];
    }
    
    /**
     *  The address and name from the mail will send
     *
     * @param  string $from from address, default: MAIL_ADDRESS .env entry.
     * @param  mixed $name from name, default: MAIL_ADDRESS_NAME .env entry.
     * @return static
     */
    public function from(?string $from = null, ?string $name = null): static{
        $from = $from ?? $_ENV['MAIL_FROM'];
        $name = $name ?? $_ENV['MAIL_FROM_NAME'];

        $this->mailer->setFrom($from, $name);

        return $this;
    }
    
    /**
     *  Adds one or more address to the current instance
     *
     * @param  array $to An array where key is the name to use and value the address to append.
     * @return static
     */
    public function to(array $to): static{
        foreach ($to as $name => $email) {
            $this->mailer->addAddress($email, $name);
        }

        return $this;
    }
    
    /**
     *  Subject of the mail to include.
     *
     * @param  string $subject string including the subject to use.
     * @return static
     */
    public function subject(string $subject) : static{
        $this->mailer->Subject = $subject;
        return $this;
    }
    
    /**
     *  Enable HTML template and use it to incrust the content in the mail to send.
     *
     * @param  string $templatePath The relative path of the template file inside Core/Mailer/Templates/
     * @param array $values The values to incrust inside the template if they needed.
     * @param  string $charset Charset to use inside the content. Default: UTF-8
     * @return static
     */
    public function useTemplate(string $templatePath, array $values = [], string $charset = 'UTF-8'): static{
        $templatePath = Mailer::TEMPLATE_MAIL_PATH . '/' . $templatePath . '.php';
        $content = null;
        
        if(!file_exists(Mailer::TEMPLATE_MAIL_PATH) || !file_exists($templatePath)) 
            throw new Error("It seems Core/Mailer/Templates or the template $templatePath that you write doesn't exists.");
        
        $this->mailer->isHTML();
        $this->mailer->CharSet = $charset;

        ob_start();
            extract($values);
            include $templatePath;
        $content = ob_get_clean();

        $this->mailer->Body = $content;

        return $this;
    }

    public function send() : bool{
        $result = $this->mailer->send();

        if(!$result)
            throw new Error("Can't send the mail correctly, check your server connection.");

        $this->mailer = null;

        return $result;
    }
}