<?php

namespace Laravel\Prompts\Themes\Default;

use Laravel\Prompts\MultiSelectPrompt;

class MultiSelectPromptRenderer extends Renderer
{
    use Concerns\DrawsBoxes;
    use Concerns\DrawsScrollbars;

    /**
     * Render the multiselect prompt.
     */
    public function __invoke(MultiSelectPrompt $prompt): string
    {
        return match ($prompt->state) {
            'submit' => $this
                ->box($this->dim($prompt->label), $this->dim($this->renderSelectedOptions($prompt))),

            'cancel' => $this
                ->box($prompt->label, $this->strikethrough($this->dim($this->renderSelectedOptions($prompt))), color: 'red')
                ->error('Cancelled.'),

            'error' => $this
                ->box($prompt->label, $this->renderOptions($prompt), color: 'yellow')
                ->warning($prompt->error),

            default => $this
                ->box($this->cyan($prompt->label), $this->renderOptions($prompt))
                ->newLine(), // Space for errors
        };
    }

    /**
     * Render the options.
     */
    protected function renderOptions(MultiSelectPrompt $prompt): string
    {
        return $this->scroll(
            collect($prompt->options)
                ->values()
                ->map(function ($label, $index) use ($prompt) {
                    $active = $index === $prompt->highlighted;
                    if (array_is_list($prompt->options)) {
                        $value = $prompt->options[$index];
                    } else {
                        $value = array_keys($prompt->options)[$index];
                    }
                    $selected = in_array($value, $prompt->value());

                    return match (true) {
                        $active && $selected => "{$this->cyan('› ◼')} {$this->format($label)}  ",
                        $active => "{$this->cyan('›')} ◻ {$this->format($label)}  ",
                        $selected => "  {$this->cyan('◼')} {$this->dim($this->format($label))}  ",
                        default => "  {$this->dim('◻')} {$this->dim($this->format($label))}  ",
                    };
                }),
            $prompt->highlighted,
            $prompt->scroll,
            $this->longest($prompt->options, padding: 6)
        )->implode(PHP_EOL);
    }

    /**
     * Render the selected options.
     */
    protected function renderSelectedOptions(MultiSelectPrompt $prompt): string
    {
        if (count($prompt->labels()) === 0) {
            return $this->gray('None');
        }

        return implode(', ', array_map(fn ($label) => $this->format($label), $prompt->labels()));
    }
}
