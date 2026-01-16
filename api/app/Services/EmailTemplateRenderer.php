<?php

namespace App\Services;

class EmailTemplateRenderer
{
    /**
     * Render an email template by replacing variable placeholders.
     *
     * Supports:
     * - Simple variables: {{name}}
     * - Nested variables: {{sender.name}}
     * - Missing variables default to empty string
     *
     * @param string $template Template string with {{variable}} placeholders
     * @param array $variables Associative array of variable values
     * @return string Rendered template
     */
    public function render(string $template, array $variables): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($variables) {
            $key = trim($matches[1]);

            // Handle nested variables (e.g., "sender.name")
            if (str_contains($key, '.')) {
                return $this->resolveNestedVariable($key, $variables);
            }

            // Handle simple variables
            if (!isset($variables[$key])) {
                return '';
            }

            $value = $variables[$key];

            // Handle arrays gracefully
            if (is_array($value)) {
                return 'Array';
            }

            return (string) $value;
        }, $template);
    }

    /**
     * Resolve nested variable like "sender.name" from variables array.
     *
     * @param string $key Dot-notation key (e.g., "sender.name")
     * @param array $variables Variables array
     * @return string Resolved value or empty string
     */
    private function resolveNestedVariable(string $key, array $variables): string
    {
        $keys = explode('.', $key);
        $value = $variables;

        foreach ($keys as $segment) {
            if (!is_array($value) || !isset($value[$segment])) {
                return '';
            }
            $value = $value[$segment];
        }

        // Handle arrays gracefully
        if (is_array($value)) {
            return 'Array';
        }

        return (string) $value;
    }
}
