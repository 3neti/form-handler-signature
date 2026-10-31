export function canvasDimensions(
  width: number,
  height: number,
  pixelRatio: number,
  maximum: number,
) {
  const scale = Math.min(pixelRatio || 1, maximum / width, maximum / height);

  return {
    width: Math.max(1, Math.floor(width * scale)),
    height: Math.max(1, Math.floor(height * scale)),
  };
}
