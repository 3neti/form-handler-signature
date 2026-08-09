<script setup lang="ts">
import { ref, onMounted, computed } from "vue";
import { Button } from "@/components/ui/button";
import { PenTool } from "lucide-vue-next";
import FormFlowActions from "@/pages/form-flow/core/components/FormFlowActions.vue";
import FormFlowScreen from "@/pages/form-flow/core/components/FormFlowScreen.vue";
import type { FormFlowUiVariant } from "@/pages/form-flow/core/components/formFlowUiVariant";

export interface SignatureConfig {
  width?: number;
  height?: number;
  quality?: number;
  format?: string;
  line_width?: number;
  line_color?: string;
  line_cap?: "butt" | "round" | "square";
  line_join?: "bevel" | "round" | "miter";
}

export interface SignatureData {
  signature: string;
  image?: string;
  width: number;
  height: number;
  format: string;
}

interface PackageVersion {
  name: string;
  version: string;
}

interface Props {
  config?: SignatureConfig;
  uiVariant?: FormFlowUiVariant | string | null;
  actionPlacement?: "inline" | "bottom" | "bottom_sticky" | "viewport_bottom" | string | null;
  uiLayout?: Record<string, unknown> | null;
  appName?: string | null;
  appLogo?: string | null;
  packageVersions?: PackageVersion[] | Record<string, string> | null;
  showPackageVersions?: boolean;
  previewMode?: boolean;
}

interface Emits {
  (e: "submit", value: SignatureData): void;
  (e: "cancel"): void;
}

const props = withDefaults(defineProps<Props>(), {
  config: () => ({}),
  uiVariant: "default",
  actionPlacement: null,
  uiLayout: null,
  appName: null,
  appLogo: null,
  packageVersions: null,
  showPackageVersions: false,
  previewMode: false,
});

const emit = defineEmits<Emits>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
const isDrawing = ref(false);
const hasSignature = ref(false);

const height = computed(() => props.config?.height ?? 256);
const quality = computed(() => props.config?.quality ?? 0.85);
const format = computed(() => props.config?.format ?? "image/png");
const lineWidth = computed(() => props.config?.line_width ?? 2);
const lineColor = computed(() => props.config?.line_color ?? "#000000");
const lineCap = computed(() => props.config?.line_cap ?? "round");
const lineJoin = computed(() => props.config?.line_join ?? "round");
const isImmersive = computed(() => props.uiVariant === "immersive");
const formClass = computed(() =>
  isImmersive.value ? "flex flex-1 flex-col gap-4" : "space-y-6",
);
const canvasFrameClass = computed(() =>
  isImmersive.value
    ? "relative min-h-[50vh] flex-1 rounded-md border-2 border-dashed border-gray-300 bg-white"
    : "relative rounded-md border-2 border-dashed border-gray-300 bg-white",
);
const canvasStyle = computed(() => ({
  width: "100%",
  height: isImmersive.value ? "100%" : `${height.value}px`,
}));

let ctx: CanvasRenderingContext2D | null = null;

function initCanvas() {
  if (!canvasRef.value) return;

  ctx = canvasRef.value.getContext("2d");
  if (!ctx) return;

  // Set canvas size with device pixel ratio for high-DPI displays
  const rect = canvasRef.value.getBoundingClientRect();
  canvasRef.value.width = rect.width * window.devicePixelRatio;
  canvasRef.value.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

  // Set drawing style from config
  ctx.strokeStyle = lineColor.value;
  ctx.lineWidth = lineWidth.value;
  ctx.lineCap = lineCap.value;
  ctx.lineJoin = lineJoin.value;
}

function startDrawing(e: MouseEvent | TouchEvent) {
  if (!ctx || !canvasRef.value) return;
  isDrawing.value = true;

  const rect = canvasRef.value.getBoundingClientRect();
  const x = ("touches" in e ? e.touches[0].clientX : e.clientX) - rect.left;
  const y = ("touches" in e ? e.touches[0].clientY : e.clientY) - rect.top;

  ctx.beginPath();
  ctx.moveTo(x, y);
}

function draw(e: MouseEvent | TouchEvent) {
  if (!isDrawing.value || !ctx || !canvasRef.value) return;
  e.preventDefault();

  hasSignature.value = true;

  const rect = canvasRef.value.getBoundingClientRect();
  const x = ("touches" in e ? e.touches[0].clientX : e.clientX) - rect.left;
  const y = ("touches" in e ? e.touches[0].clientY : e.clientY) - rect.top;

  ctx.lineTo(x, y);
  ctx.stroke();
}

function stopDrawing() {
  isDrawing.value = false;
}

function clearSignature() {
  if (!ctx || !canvasRef.value) return;
  ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height);
  hasSignature.value = false;
}

function handleSubmit() {
  if (!canvasRef.value || !hasSignature.value) return;

  // Convert canvas to base64 data URL with configured quality
  const image = canvasRef.value.toDataURL(format.value, quality.value);

  const signatureData: SignatureData = {
    signature: image,
    image,
    width: canvasRef.value.width,
    height: canvasRef.value.height,
    format: format.value,
  };

  emit("submit", signatureData);
}

function handleCancel() {
  emit("cancel");
}

onMounted(() => {
  setTimeout(initCanvas, 100);
});
</script>

<template>
  <FormFlowScreen
    title="Signature Required"
    description="Please sign in the box below using your mouse or touchscreen"
    :variant="uiVariant"
    :app-name="appName"
    :app-logo="appLogo"
    :package-versions="packageVersions"
    :show-package-versions="showPackageVersions"
    version-context="signature"
  >
    <template #icon>
      <PenTool class="h-5 w-5" />
    </template>

    <form @submit.prevent="handleSubmit" :class="formClass">
      <!-- Signature Canvas -->
      <div class="flex min-h-0 flex-1 flex-col gap-2">
        <div :class="canvasFrameClass">
          <canvas
            ref="canvasRef"
            :style="canvasStyle"
            class="h-full w-full touch-none cursor-crosshair"
            @mousedown="startDrawing"
            @mousemove="draw"
            @mouseup="stopDrawing"
            @mouseleave="stopDrawing"
            @touchstart.prevent="startDrawing"
            @touchmove.prevent="draw"
            @touchend="stopDrawing"
          />
          <div
            v-if="!hasSignature"
            class="pointer-events-none absolute inset-0 flex items-center justify-center text-gray-400"
          >
            Sign here
          </div>
        </div>
        <Button
          type="button"
          variant="outline"
          size="sm"
          @click="clearSignature"
          :disabled="!hasSignature"
        >
          Clear Signature
        </Button>
      </div>

      <FormFlowActions
        :variant="uiVariant"
        :action-placement="actionPlacement"
        :primary-disabled="!hasSignature"
        @secondary="handleCancel"
      />
    </form>
  </FormFlowScreen>
</template>
