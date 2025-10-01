<?php
// Expected variables:
// $label: string
// $id: string
// $name: string
// $type: string ('text', 'select', 'file', 'textarea')
// $value: string
// $options: array (only for select)
// $class: string
// placeholder: string
?>

<div class="col-md-4">

    @if ($type === 'file')
        <label for="{{ $id }}" class="btn btn-sm btn-outline-secondary mb-0 mx-auto" title="Upload Image">
            <i class="bi bi-camera-fill text-success"></i>
        </label>
    @else
        <label for="{{ $id }}" class="form-label"><b>{{ $label }}</b> </label>
    @endif

    @if ($type === 'select')
        <select class="{{ $class }} " id="{{ $id }}" name="{{ $name }}"
            data-original="{{ $value }}">
            @foreach ($options as $option)
                <option value="{{ $option }}" <?= $option === $value ? 'selected' : '' ?>>{{ $option }}
                </option>
            @endforeach
            <small id="{{ $id }}_error"></small>
        </select>
    @elseif ($type === 'textarea')
        <textarea class="{{ $class }}" id="{{ $id }}" name="{{ $name }}"
            data-original="{{ $value }}" placeholder="{{ $placeholder }}">{{ $value }}</textarea>
        <small id="{{ $id }}_error"></small>
    @elseif ($type === 'file')
        <input type="file" class="{{ $class }}" id="{{ $id }}" name="{{ $name }}"
            style="display: none;" accept="image/*">
        <small id="{{ $id }}_error"></small>
    @elseif($type === 'button')
        <button type="button" class="{{ $class }}" id="{{ $id }}" name="{{ $name }}"
            data-bs-dismiss="modal"> {{ $buttonValue }}</button>
    @elseif($type === 'hidden')
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @else
        <input type="{{ $type }}" class="{{ $class }}" id="{{ $id }}"
            name="{{ $name }}" value="{{ $value }}" data-original="{{ $value }}" placeholder="{{ $placeholder }}">
        <small id="{{ $id }}_error"></small>
    @endif
</div>
