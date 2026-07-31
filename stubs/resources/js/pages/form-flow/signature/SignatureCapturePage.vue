<script setup lang="ts">
import { router } from "@inertiajs/vue3";
import SignatureCapture, {
  type SignatureData,
  type SignatureConfig,
} from "./components/SignatureCapture.vue";
import PublicLayout from "@/layouts/PublicLayout.vue";
import type { FormFlowUiVariant } from "@/pages/form-flow/core/components/formFlowUiVariant";

interface Props {
  flow_id: string;
  step: string;
  config?: SignatureConfig;
  ui_variant?: FormFlowUiVariant | string | null;
}

const props = defineProps<Props>();

function handleSubmit(signatureData: SignatureData) {
  // Submit to FormFlowController
  router.post(`/form-flow/${props.flow_id}/step/${props.step}`, {
    data: signatureData as unknown as Record<string, any>,
  });
}

function handleCancel() {
  // Cancel the flow
  router.post(`/form-flow/${props.flow_id}/cancel`);
}
</script>

<template>
  <PublicLayout>
    <SignatureCapture
      :config="config"
      :ui-variant="ui_variant"
      @submit="handleSubmit"
      @cancel="handleCancel"
    />
  </PublicLayout>
</template>
