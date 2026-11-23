<?php

namespace App\Livewire\Societe;

use App\Models\Societe;
use App\Support\MenuItems;
use Illuminate\Support\Str;
use Livewire\Component;

class MenuEditor extends Component
{
    public array $sections = [];

    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->isAdmin() || auth()->user()->hasPermission('gestion_societe'), 403);

        $this->loadFromConfig(Societe::current()->menu_config);
    }

    protected function loadFromConfig(?array $config): void
    {
        $resolved = MenuItems::resolve($config);

        $sections = [];
        foreach ($resolved['sections'] as $key => $section) {
            $sections[] = [
                'key' => $key,
                'label' => $section['label'],
                'icon' => $section['icon'],
                'order' => $section['order'],
            ];
        }
        $this->sections = $sections;

        $items = [];
        foreach ($resolved['unsectioned'] as $item) {
            $items[] = $this->rowFromResolvedItem($item);
        }
        foreach ($resolved['sections'] as $section) {
            foreach ($section['items'] as $item) {
                $items[] = $this->rowFromResolvedItem($item);
            }
        }
        $this->items = $items;
    }

    protected function rowFromResolvedItem(array $item): array
    {
        return [
            'key' => $item['key'],
            'route' => $item['route'],
            'defaultLabel' => MenuItems::ALL[$item['key']]['label'],
            'label' => $item['label'],
            'section' => $item['section'] === null ? '' : $item['section'],
            'order' => $item['order'],
        ];
    }

    public function addSection(): void
    {
        $maxOrder = collect($this->sections)->max('order') ?? 0;

        $this->sections[] = [
            'key' => 'section_'.Str::random(8),
            'label' => 'Nouvelle section',
            'icon' => '📎',
            'order' => $maxOrder + 10,
        ];
    }

    public function removeSection(string $key): void
    {
        $this->sections = array_values(array_filter($this->sections, fn ($s) => $s['key'] !== $key));

        foreach ($this->items as &$item) {
            if ($item['section'] === $key) {
                $item['section'] = '';
            }
        }
    }

    public function moveSectionUp(string $key): void
    {
        $this->swapSectionOrder($key, -1);
    }

    public function moveSectionDown(string $key): void
    {
        $this->swapSectionOrder($key, 1);
    }

    protected function swapSectionOrder(string $key, int $direction): void
    {
        $ordered = collect($this->sections)->sortBy('order')->values();
        $index = $ordered->search(fn ($s) => $s['key'] === $key);

        if ($index === false) {
            return;
        }

        $targetIndex = $index + $direction;

        if ($targetIndex < 0 || $targetIndex >= $ordered->count()) {
            return;
        }

        $current = $ordered[$index];
        $target = $ordered[$targetIndex];

        [$current['order'], $target['order']] = [$target['order'], $current['order']];

        $ordered[$index] = $current;
        $ordered[$targetIndex] = $target;

        $this->sections = $ordered->values()->all();
    }

    public function resetToDefault(): void
    {
        $this->loadFromConfig(null);
    }

    public function save(): void
    {
        $sectionsOut = [];
        foreach ($this->sections as $section) {
            $sectionsOut[$section['key']] = [
                'label' => trim((string) $section['label']) !== '' ? trim($section['label']) : 'Section',
                'icon' => $section['icon'] ?: '📎',
                'order' => (int) $section['order'],
            ];
        }

        $itemsOut = [];
        foreach ($this->items as $item) {
            $itemsOut[$item['key']] = [
                'label' => trim((string) $item['label']) !== '' ? trim($item['label']) : $item['defaultLabel'],
                'section' => $item['section'],
                'order' => (int) $item['order'],
            ];
        }

        Societe::current()->update([
            'menu_config' => ['sections' => $sectionsOut, 'items' => $itemsOut],
        ]);

        $this->loadFromConfig(Societe::current()->menu_config);

        session()->flash('menu_success', 'Menu lateral enregistre.');
    }

    public function render()
    {
        $sectionOptions = collect($this->sections)->sortBy('order')->values();

        return view('livewire.societe.menu-editor', [
            'sectionOptions' => $sectionOptions,
        ]);
    }
}
