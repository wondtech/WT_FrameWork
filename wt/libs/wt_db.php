<?php
/***********************************************************************
 *          @Project    : WT FrameWork
 *          @version    : 2.0
 *          @author     : Mogbil Sourketti info[@]wondtech.com
 *          @copyright  : 2020 WondTech for Integrated Digital Solutions
 *          @link       : http://www.wondtech.com
 *          @package    : WT FrameWork (2.0) — Improved
 ************************************************************************/

namespace WT\LIBS;

class Wt_DB
{
    private string $localHost;
    private string $dbName;
    private string $userName;
    private string $passWord;
    private ?\PDO $pdo = null;

    private function __construct()
    {
        $this->localHost = $_ENV['DB_HOST']     ?? '127.0.0.1';
        $this->dbName    = $_ENV['DB_NAME']     ?? '';
        $this->userName  = $_ENV['DB_USER']     ?? '';
        $this->passWord  = $_ENV['DB_PASSWORD'] ?? '';

        $this->connect();
    }

    public static function getInstance(): static
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }

    private function connect(): void
    {
        try {
            $dsn = 'mysql:host=' . $this->localHost
                . ';dbname='   . $this->dbName
                . ';charset=utf8mb4';

            $this->pdo = new \PDO($dsn, $this->userName, $this->passWord, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false, // Prepared Statements حقيقية
            ]);

            $this->pdo->exec("SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'");
            // Align MySQL's clock with PHP's so NOW() matches PHP date() — keeps
            // short-lived expiry checks (OTP/token: expires_at > NOW()) correct
            // regardless of the server's default MySQL time zone.
            $this->pdo->exec("SET time_zone = '" . (new \DateTime())->format('P') . "'");

        } catch (\PDOException $e) {
            error_log('[Wt_DB] ' . $e->getMessage());
            throw new \RuntimeException(
                'Database connection failed.',
                (int) $e->getCode(),
                $e
            );
        }
    }

    public function getPDO(): \PDO
    {
        return $this->pdo;
    }

    private function __clone() {}
}