<?php

namespace ModulesPress\Core\Http;

use ReflectionParameter;
use Adbar\Dot;
use ClassTransformer\Hydrator;
use ModulesPress\Common\Exceptions\FrameworkException\ValidationException;
use ModulesPress\Common\Exceptions\HttpException\BadRequestHttpException;
use ModulesPress\Common\Exceptions\HttpException\UnprocessableEntityHttpException;
use ModulesPress\Foundation\Http\Attributes\Body;
use ModulesPress\Foundation\Http\Attributes\Param;
use ModulesPress\Foundation\Http\Attributes\Query;
use Symfony\Component\Validator\ValidatorBuilder;

/**
 * A class responsible for parsing and validating request parameters that are annotated with specific attributes like
 * [#Body], [#Query], and [#Param].
 */
final class RequestParameterParser
{
    /**
     * Parses and validates a parameter annotated with the [#Body] attribute.
     *
     * @param ReflectionParameter $paramRef Reflection of the parameter being parsed.
     * @param array $json Parsed JSON body from the request.
     * @param Body $body Attribute metadata for the parameter.
     * @param array $pipes Transformation pipes to apply to the value.
     * @return mixed The parsed and validated value.
     * @throws BadRequestHttpException if the parameter is missing or invalid.
     */
    public function parseBodyParameter(ReflectionParameter $paramRef, array $json, Body $body, $pipes)
    {
        $key = $body->getKey();
        $rules = $body->getRules();
        $casting = $body->isCastingEnable();

        return $this->parseParameter($paramRef, $json, $key, $rules, $casting, $pipes);
    }

    /**
     * Parses and validates a parameter annotated with the [#Query] attribute.
     *
     * @param ReflectionParameter $paramRef Reflection of the parameter being parsed.
     * @param array $json Parsed query parameters from the request.
     * @param Query $query Attribute metadata for the parameter.
     * @param array $pipes Transformation pipes to apply to the value.
     * @return mixed The parsed and validated value.
     * @throws BadRequestHttpException if the parameter is missing or invalid.
     */
    public function parseQueryParameter(ReflectionParameter $paramRef, array $json, Query $query, $pipes)
    {
        $key = $query->getKey();
        $rules = $query->getRules();
        $casting = $query->isCastingEnable();

        return $this->parseParameter($paramRef, $json, $key, $rules, $casting, $pipes);
    }

    /**
     * Parses and validates a parameter annotated with the [#Param] attribute.
     *
     * @param ReflectionParameter $paramRef Reflection of the parameter being parsed.
     * @param array $json Parsed route parameters from the request.
     * @param Param $param Attribute metadata for the parameter.
     * @param array $pipes Transformation pipes to apply to the value.
     * @return mixed The parsed and validated value.
     * @throws BadRequestHttpException if the parameter is missing or invalid.
     */
    public function parseParamParameter(ReflectionParameter $paramRef, array $json, Param $param, $pipes)
    {
        $key = $param->getKey();
        $rules = $param->getRules();
        $casting = $param->isCastingEnable();

        return $this->parseParameter($paramRef, $json, $key, $rules, $casting, $pipes);
    }

    /**
     * Parses a request parameter based on its metadata, applies transformation pipes, and validates it.
     *
     * @param ReflectionParameter $paramRef Reflection of the parameter being parsed.
     * @param array $json Parsed data source (body, query, or path).
     * @param string $key The key used to extract the parameter from the data.
     * @param array $rules Validation rules for the parameter.
     * @param bool $casting Whether type casting is enabled.
     * @param array $pipes Transformation pipes to apply to the value.
     * @return mixed The parsed and validated value.
     * @throws BadRequestHttpException if the parameter is invalid or missing.
     */
    private function parseParameter(
        ReflectionParameter $paramRef,
        array $json,
        string $key,
        array $rules,
        bool $casting,
        array $pipes
    ) {
        $paramType = $paramRef->getType();

        // Check if the parameter key exists in the provided JSON
        if ($key) {
            $dotBody = new Dot($json);
            if (!$dotBody->has($key)) {
                if ($paramRef->isDefaultValueAvailable()) {
                    $this->validateValue($paramRef->getDefaultValue(), $rules, $key);
                    return $paramRef->getDefaultValue();
                }
                throw new BadRequestHttpException("$key is required");
            }
            $value = $dotBody->get($key);
        } else {
            $value = $json;
        }

        // Apply transformation pipes if any
        foreach ($pipes as $pipe) {
            $value = $pipe->transform($value);
        }

        // Validate the value and process based on type
        if (!$paramType) {
            $this->validateValue($value, $rules, $key);
            return $value;
        }

        if ($paramType->isBuiltin()) {
            return $this->handleBuiltInTypeValue($paramType, $value, $key, $rules, $casting);
        } else {
            return $this->handleNonBuiltInTypeValue($paramType, $value);
        }
    }

    /**
     * Handles validation and type casting for built-in PHP types.
     *
     * @param mixed $paramType The reflection type of the parameter.
     * @param mixed $value The value being processed.
     * @param string $key The parameter key.
     * @param array $rules Validation rules.
     * @param bool $casting Whether type casting is enabled.
     * @return mixed The processed value.
     * @throws BadRequestHttpException if the type does not match and casting is not enabled.
     */
    private function handleBuiltInTypeValue($paramType, $value, $key, array $rules, bool $casting)
    {
        $expectedType = $paramType->getName();
        $actualType = gettype($value);
        if ($actualType !== $expectedType) {
            $castedValue = $casting ? $this->typeCast($value, $expectedType) : null;
            if ($castedValue !== null) {
                $value = $castedValue;
            } else {
                throw new BadRequestHttpException(
                    "Expected a value of type '$expectedType' for parameter '$key', but got '" . gettype($value) . "'"
                );
            }
        }

        $this->validateValue($value, $rules, $key);
        return $value;
    }

    /**
     * Handles non-built-in types by creating an instance of the DTO (Data Transfer Object).
     *
     * @param mixed $paramType The reflection type of the parameter.
     * @param mixed $value The value to be processed.
     * @return mixed The created DTO.
     * @throws BadRequestHttpException if the value is invalid or cannot be casted.
     */
    private function handleNonBuiltInTypeValue($paramType, $value)
    {
        if (!is_array($value)) {
            throw new BadRequestHttpException("Expected an array or object.");
        }

        return $this->createDTO($paramType->getName(), $value);
    }

    /**
     * Creates a Data Transfer Object (DTO) from the provided class name and parameters.
     *
     * @param string $dtoClassName The class name of the DTO.
     * @param array $jsonParams The parameters to populate the DTO.
     * @return mixed The created DTO.
     */
    private function createDTO(string $dtoClassName, array $jsonParams)
    {
        $dtoObject = (new Hydrator())->create($dtoClassName, $jsonParams);
        $this->validateDTO($dtoClassName, $dtoObject);
        return $dtoObject;
    }

    /**
     * Validates a DTO (Data Transfer Object) using Symfony's Validator component.
     *
     * @param string $dtoClassName The DTO class name.
     * @param mixed $dtoObject The DTO instance.
     * @throws ValidationException if validation fails.
     */
    private function validateDTO(string $dtoClassName, mixed $dtoObject)
    {
        $validator = new ValidatorBuilder();
        $validator = $validator->enableAttributeMapping()->getValidator();
        $errors = $validator->validate($dtoObject);
        if (count($errors) > 0) {
            $validationException = new ValidationException();
            foreach ($errors as $error) {
                $validationException->attachError($error->getPropertyPath(), $error->getMessage());
            }
            throw $validationException;
        }
    }

    /**
     * Validates the parameter's value based on the provided rules.
     *
     * @param mixed $value The value to validate.
     * @param array $rules The validation rules.
     * @param string $key The parameter key.
     * @throws ValidationException if validation fails.
     */
    private function validateValue($value, array $rules, $key)
    {
        if (empty($rules)) {
            return;
        }
        $key !== "" ? $key : "path";
        $validator = new ValidatorBuilder();
        $validator = $validator->getValidator();
        $errors = $validator->validate($value, $rules);
        if (count($errors) > 0) {
            $validationException = new ValidationException();
            foreach ($errors as $error) {
                $path = $error->getPropertyPath();
                $validationException->attachError($path !== "" ? $path : $key, $error->getMessage());
            }
            throw $validationException;
        }
    }

    /**
     * Attempts to cast a value to the expected type.
     *
     * @param mixed $value The value to cast.
     * @param string $expectedType The expected type as a string.
     * @return mixed The casted value or null if casting fails.
     */
    private function typeCast($value, string $expectedType)
    {
        switch (strtolower($expectedType)) {
            case 'int':
            case 'integer':
                if (is_numeric($value) && strpos((string)$value, '.') === false) {
                    return (int)$value;
                }
                if (is_bool($value)) {
                    return (int)$value;
                }
                return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);

            case 'float':
            case 'double':
                if (is_numeric($value)) {
                    return (float)$value;
                }
                return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);

            case 'string':
                if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
                    return (string)$value;
                }
                return null;

            case 'bool':
            case 'boolean':
                if (is_bool($value)) {
                    return $value;
                }
                if (is_numeric($value)) {
                    return (bool)$value;
                }
                if (is_string($value)) {
                    $value = strtolower($value);
                    if (in_array($value, ['true', '1', 'on', 'yes'], true)) {
                        return true;
                    }
                    if (in_array($value, ['false', '0', 'off', 'no'], true)) {
                        return false;
                    }
                }
                return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            case 'array':
                if (is_array($value)) {
                    return $value;
                }
                if (is_object($value)) {
                    return (array)$value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return $decoded;
                    }
                }
                return null;

            case 'object':
                if (is_object($value)) {
                    return $value;
                }
                if (is_array($value)) {
                    return (object)$value;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value);
                    if (json_last_error() === JSON_ERROR_NONE && is_object($decoded)) {
                        return $decoded;
                    }
                }
                return null;

            case 'null':
                return null;

            case 'mixed':
                return $value;

            default:
                // For non-built-in types, attempt to create an instance if it's a class
                if (class_exists($expectedType)) {
                    try {
                        return new $expectedType($value);
                    } catch (\Throwable $e) {
                        return null;
                    }
                }
                return null;
        }
    }
}
