import { test } from "node:test";
import assert from "node:assert/strict";
import { canvasDimensions } from "../stubs/resources/js/pages/form-flow/signature/components/canvasDimensions.ts";
import { signatureVisitOptions } from "../stubs/resources/js/pages/form-flow/signature/components/signatureVisitOptions.ts";

test("preserves native resolution for a high density phone", () => {
  assert.deepEqual(canvasDimensions(402, 377, 3, 4096), {
    width: 1206,
    height: 1131,
  });
});

test("submission failure callbacks request state preservation and allow retry", () => {
  for (const failure of ["onError", "onNetworkError", "onHttpException"]) {
    let message = null;
    let processing = true;
    const options = signatureVisitOptions(
      (value) => {
        message = value;
      },
      () => {
        processing = false;
      },
    );
    assert.equal(options.preserveState, true);
    options[failure]();
    options.onFinish();
    assert.ok(message);
    assert.equal(processing, false);
  }
});

test("an unconfirmed request is not presented as successful", () => {
  let message = null;
  const options = signatureVisitOptions(
    (value) => {
      message = value;
    },
    () => {},
  );
  options.onFinish();
  assert.match(message, /couldn't confirm/);
});

test("successful submission finishes without an error", () => {
  let message = null;
  const options = signatureVisitOptions(
    (value) => {
      message = value;
    },
    () => {},
  );
  options.onSuccess();
  options.onFinish();
  assert.equal(message, null);
});

test("bounds both landscape and portrait images while keeping their aspect ratio", () => {
  assert.deepEqual(canvasDimensions(2000, 1000, 3, 4096), {
    width: 4096,
    height: 2048,
  });
  assert.deepEqual(canvasDimensions(1000, 2000, 3, 4096), {
    width: 2048,
    height: 4096,
  });
});

test("handles fractional display scales without exceeding the limit", () => {
  const size = canvasDimensions(2000.5, 3000.5, 2.625, 4096);
  assert.ok(size.width <= 4096 && size.height <= 4096);
  assert.ok(Math.abs(size.width / size.height - 2000.5 / 3000.5) < 0.001);
});
