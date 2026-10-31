export function signatureVisitOptions(
  setError: (message: string) => void,
  finish: () => void,
) {
  let responded = false;
  const fail = (message: string) => {
    responded = true;
    setError(message);
  };

  return {
    preserveState: true,
    onSuccess: () => {
      responded = true;
    },
    onError: () => {
      fail("We couldn't accept your signature. Please try again.");
    },
    onNetworkError: () => {
      fail("Connection lost. Check your connection and try again.");
      return false;
    },
    onHttpException: () => {
      fail("We couldn't submit your signature. Please try again.");
      return false;
    },
    onFinish: () => {
      if (!responded)
        setError(
          "We couldn't confirm your signature. Please check your connection and try again.",
        );
      finish();
    },
  };
}
