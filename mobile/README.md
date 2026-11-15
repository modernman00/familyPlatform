# Family Platform Mobile Wrapper (Google Play & Apple App Store)

- **Bundle / Package ID:** `com.familyplatform.app`
- **Production Origin:** `https://myfamilyplatform.com`

## Prerequisites
1. **Android:** Install Android Studio + Android SDK (API 34/35). Generate a release keystore (`keytool -genkey -v -keystore release.jks ...`).
2. **iOS:** Install Xcode from the Mac App Store (`sudo xcode-select -s /Applications/Xcode.app/Contents/Developer`).
3. **Deep Links:**
   - Replace `REPLACE_WITH_PLAY_APP_SIGNING_SHA256_FINGERPRINT` in `public/.well-known/assetlinks.json`.
   - Replace `REPLACE_WITH_APPLE_TEAM_ID` in `public/.well-known/apple-app-site-association`.

## Local Commands
```bash
cd mobile
npm install
npx cap sync
npx cap open android   # Build .aab via Build > Generate Signed Bundle / APK
npx cap open ios       # Archive & upload via Xcode Product > Archive
```

## App Store & Play Store Compliance Checklist
- [ ] **Privacy Policy:** Live at `https://myfamilyplatform.com/privacy`
- [ ] **In-App Account Deletion:** Verified inside authenticated settings (required by Apple 5.1.1(v) and Google Play Data Safety)
- [ ] **Sign in with Apple (Guideline 4.8):** Required if Google/social login is enabled on iOS
- [ ] **Reviewer Demo Account:** Provide non-OTP test credentials in App Store Connect & Play Console
- [ ] **Apple Guideline 4.2:** Ensure native plugins (Push Notifications, Haptics, Share Sheet, Biometrics) are active and tested
