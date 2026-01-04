# Reusable Chat - Current Tasks

## Bug Fixes

- [x] Fix `window.open()` reference error in watercloset attachment click handler
  - Changed direct `window.open()` call in Vue template to use `openAttachment()` method
  - Committed and pushed to watercloset-new

## Feature Additions

### Image Lightbox Viewer
- [x] Add lightbox modal HTML structure to demo.vue
- [x] Add lightbox state variables
- [x] Add openImage/closeLightbox functions
- [x] Add lightbox CSS styles
- [ ] Test lightbox functionality

### Branding
- [x] Add "Powered by Reusable Chat" badge to demo chat screen
- [x] Add branding CSS styles
- [ ] Consider making branding configurable per workspace

### File Handling
- [ ] Ensure PDFs open in browser if possible
- [ ] Add proper download buttons for non-image files
- [ ] Test file upload and download flow

## Documentation

- [x] Update README.md with latest features
- [x] Document the widget API endpoints
- [x] Document WebSocket events
- [x] Explain watercloset integration architecture
- [x] Add setup guide for new integrations

## Architecture Explanation (for user)

After completing tasks, provide explanation of:
- How reusable-chat works
- How watercloset integrates with it
- How much custom code was required

## Deployment

- [ ] Deploy reusable-chat API changes
- [x] Deploy reusable-chat dashboard changes (pushed to main, auto-deploys)
- [x] Verify watercloset deployment completed
- [ ] Test end-to-end in production

---

## Progress Log

### 2026-01-04
- Fixed `window.open()` bug in watercloset/messages/index.vue
- Added lightbox viewer to reusable-chat demo page
- Added "Powered by Reusable Chat" branding badge
- Deployed changes to production
