@props(['href' => '#', 'class' => ''])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => "btn-save {$class}"]) }}
>
    {{ $slot }}
</a>
<style>
    .btn-save {
      padding: 1.125rem 2rem;
      background: linear-gradient(135deg, #14368b 0%, #4f46e5 100%);
      color: white;
      border: none;
      border-radius: 0.75rem;
      font-size: 1rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.625rem;
      text-decoration: none;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(26, 91, 175, 0.4);
    }

    .btn-save:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none;
    }

    .btn-save svg {
      width: 20px;
      height: 20px;
    }
</style>
