// Lightweight SSE helper with auto-reconnect
export function createSSE(url, { onOpen, onEventMap = {}, onMessage, onError, retryBaseMs = 1000, retryMaxMs = 10000 } = {}) {
  let es = null
  let closed = false
  let retry = retryBaseMs

  const open = () => {
    if (closed) return
    es = new EventSource(url, { withCredentials: false })

    es.onopen = () => {
      retry = retryBaseMs
      onOpen && onOpen()
    }

    es.onerror = (e) => {
      onError && onError(e)
      // reconnect with backoff
      try { es.close() } catch {}
      if (closed) return
      setTimeout(open, retry)
      retry = Math.min(retry * 2, retryMaxMs)
    }

    if (onMessage) {
      es.onmessage = (ev) => {
        onMessage(ev)
      }
    }

    // named events
    Object.entries(onEventMap || {}).forEach(([evt, handler]) => {
      es.addEventListener(evt, handler)
    })
  }

  open()

  return {
    close() {
      closed = true
      try { es && es.close() } catch {}
    }
  }
}

