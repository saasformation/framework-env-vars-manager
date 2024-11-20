<?php declare(strict_types=1);

namespace SaaSFormation\Framework\EnvVarsManager\Infrastructure;

use SaaSFormation\Framework\Contracts\Infrastructure\EnvVarsManagerInterface;
use Symfony\Component\Yaml\Yaml;

class EnvVarsManager implements EnvVarsManagerInterface
{
    /** @var array<string, string|bool|int> */
    private array $envVars;

    public function __construct(private string $envVarsConfigFilePath)
    {
        /** @var array<"vars", array<string, string>> $parsedYaml */
        $parsedYaml = Yaml::parseFile($this->envVarsConfigFilePath);

        foreach($parsedYaml["vars"] as $name => $type) {
            if(!getenv($name)) {
                throw new \Exception("Env var $name is mandatory but was not provided");
            }

            $this->envVars[$name] = match ($type) {
                "int" => (int)getenv($name),
                "bool" => filter_var(getenv($name), FILTER_VALIDATE_BOOLEAN),
                default => getenv($name),
            };
        }
    }

    public function has(string $key): bool
    {
        return isset($this->envVars[$key]);
    }

    public function get(string $key): string|bool|int|float|object
    {
        if(!isset($this->envVars[$key])) {
            throw new \Exception("Env var '$key' does not exist");
        }

        return $this->envVars[$key];
    }
}