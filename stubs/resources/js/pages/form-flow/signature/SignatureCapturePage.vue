<script setup lang="ts">
import FormFlowPresentation from "@/pages/form-flow/core/components/FormFlowPresentation.vue";
import type { FormFlowPresentation as PresentationPolicy } from "@/pages/form-flow/core/components/formFlowPresentation";
import { router } from "@inertiajs/vue3";
import SignatureCapture, {
  type SignatureData,
  type SignatureConfig,
} from "./components/SignatureCapture.vue";
import PublicLayout from "@/layouts/PublicLayout.vue";
import type { FormFlowUiVariant } from "@/pages/form-flow/core/components/formFlowUiVariant";
import { ref } from "vue";
import { signatureVisitOptions } from "./components/signatureVisitOptions";

interface PackageVersion {
  name: string;
  version: string;
}

interface Props {
  presentation?: PresentationPolicy | null;
  flow_id: string;
  step: string;
  config?: SignatureConfig;
  ui_variant?: FormFlowUiVariant | string | null;
  action_placement?:
    | "inline"
    | "bottom"
    | "bottom_sticky"
    | "viewport_bottom"
    | string
    | null;
  ui_layout?: Record<string, unknown> | null;
  app_name?: string | null;
  app_logo?: string | null;
  package_versions?: PackageVersion[] | Record<string, string> | null;
  show_package_versions?: boolean;
  preview_mode?: boolean;
}

const props = defineProps<Props>();
const processing = ref(false);
const error = ref<string | null>(null);

function handleSubmit(signatureData: SignatureData) {
  if (props.preview_mode || processing.value) return;
  processing.value = true;
  error.value = null;
  // Submit to FormFlowController
  router.post(
    `/form-flow/${props.flow_id}/step/${props.step}`,
    {
      data: signatureData as unknown as Record<string, any>,
    },
    signatureVisitOptions(
      (message) => {
        error.value = message;
      },
      () => {
        processing.value = false;
      },
    ),
  );
}

function handleCancel() {
  if (props.preview_mode) return;
  // Cancel the flow
  router.post(`/form-flow/${props.flow_id}/cancel`);
}
</script>

<template>
  <FormFlowPresentation :presentation="presentation" screen="signature">
    <PublicLayout>
      <SignatureCapture
        :config="config"
        :ui-variant="ui_variant"
        :action-placement="action_placement"
        :ui-layout="ui_layout"
        :app-name="app_name"
        :app-logo="app_logo"
        :package-versions="package_versions"
        :show-package-versions="show_package_versions"
        :preview-mode="preview_mode"
        :processing="processing"
        :error="error"
        @submit="handleSubmit"
        @cancel="handleCancel"
      />
    </PublicLayout>
  </FormFlowPresentation>
</template>
