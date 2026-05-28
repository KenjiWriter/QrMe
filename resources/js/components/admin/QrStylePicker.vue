<script setup lang="ts">
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const props = defineProps<{
    color: string | null;
    eyeShape: string | null;
    /** Global / fallback colour shown as placeholder when color is null */
    globalColor?: string;
    colorError?: string;
    eyeShapeError?: string;
    /** When true the colour field is required (no global fallback) */
    required?: boolean;
}>();

const emit = defineEmits<{
    'update:color': [value: string | null];
    'update:eyeShape': [value: string | null];
}>();

const { t } = useI18n();

// ── colour ──────────────────────────────────────────────────────────────────

/**
 * Internal hex value used for display. When props.color is null we show the
 * global colour as placeholder; the actual form value stays null (= inherit).
 */
const hexInput = ref<string>(props.color ?? props.globalColor ?? '#000000');
const pickerValue = ref<string>(props.color ?? props.globalColor ?? '#000000');

watch(() => props.color, (v) => {
    if (v !== null) {
        hexInput.value = v;
        pickerValue.value = v;
    }
});

function syncFromPicker(e: Event) {
    const val = (e.target as HTMLInputElement).value;
    hexInput.value = val;
    emit('update:color', val);
}

function syncFromHex() {
    const val = hexInput.value.trim();
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) {
        pickerValue.value = val;
        emit('update:color', val);
    }
}

function clearColor() {
    hexInput.value = props.globalColor ?? '#000000';
    pickerValue.value = props.globalColor ?? '#000000';
    emit('update:color', null);
}

// ── eye shape ───────────────────────────────────────────────────────────────

const activeShape = ref<string>(props.eyeShape ?? 'square');

watch(() => props.eyeShape, (v) => {
    if (v) activeShape.value = v;
});

function selectShape(shape: string) {
    activeShape.value = shape;
    emit('update:eyeShape', shape === 'square' ? null : shape);
}

const shapes = [
    { value: 'square', label: 'qr.eye_square' },
    { value: 'round',  label: 'qr.eye_round'  },
    { value: 'dots',   label: 'qr.eye_dots'   },
] as const;
</script>

<template>
    <fieldset class="grid gap-4 border-t pt-4">
        <legend class="px-2 text-sm font-medium">{{ t('qr.style_section') }}</legend>

        <!-- Colour row -->
        <div class="grid gap-2">
            <Label>{{ t('qr.color_label') }}</Label>
            <div class="flex items-center gap-2">
                <input
                    type="color"
                    :value="pickerValue"
                    class="h-9 w-12 cursor-pointer rounded border border-input p-0.5"
                    @input="syncFromPicker"
                />
                <Input
                    v-model="hexInput"
                    class="w-32 font-mono uppercase"
                    placeholder="#000000"
                    maxlength="7"
                    @change="syncFromHex"
                    @blur="syncFromHex"
                />
                <button
                    v-if="!required && props.color !== null"
                    type="button"
                    class="text-xs text-muted-foreground hover:text-foreground underline"
                    @click="clearColor"
                >
                    {{ t('qr.use_global') }}
                </button>
                <span v-if="!required && props.color === null" class="text-xs text-muted-foreground">
                    {{ t('qr.inherits_global', { color: globalColor }) }}
                </span>
            </div>
            <InputError :message="colorError" />
        </div>

        <!-- Eye shape row -->
        <div class="grid gap-2">
            <Label>{{ t('qr.eye_shape_label') }}</Label>
            <div class="flex gap-3">
                <button
                    v-for="s in shapes"
                    :key="s.value"
                    type="button"
                    :class="[
                        'flex flex-col items-center gap-1 rounded-lg border p-2 transition-colors',
                        activeShape === s.value
                            ? 'border-primary bg-primary/10 text-primary'
                            : 'border-input hover:border-primary/50',
                    ]"
                    @click="selectShape(s.value)"
                >
                    <!-- Mini SVG preview of each eye shape -->
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <!-- Square -->
                        <template v-if="s.value === 'square'">
                            <rect x="3" y="3" width="30" height="30" rx="0" fill="none" stroke="currentColor" stroke-width="3"/>
                            <rect x="9" y="9" width="18" height="18" rx="0"/>
                        </template>
                        <!-- Round -->
                        <template v-else-if="s.value === 'round'">
                            <rect x="3" y="3" width="30" height="30" rx="8" fill="none" stroke="currentColor" stroke-width="3"/>
                            <rect x="9" y="9" width="18" height="18" rx="5"/>
                        </template>
                        <!-- Dots -->
                        <template v-else>
                            <circle cx="18" cy="18" r="15" fill="none" stroke="currentColor" stroke-width="3"/>
                            <circle cx="18" cy="18" r="8"/>
                        </template>
                    </svg>
                    <span class="text-xs">{{ t(s.label) }}</span>
                </button>
            </div>
            <InputError :message="eyeShapeError" />
        </div>
    </fieldset>
</template>
