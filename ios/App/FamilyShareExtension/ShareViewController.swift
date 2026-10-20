import UIKit
import Social
import MobileCoreServices
import UniformTypeIdentifiers

/**
 * FamilyPlatform iOS Share Sheet Extension (NSExtension).
 * Enables direct sharing of photos and videos from Apple Photos into the Family Timeline.
 */
@objc(ShareViewController)
class ShareViewController: UIViewController {

    private let activityIndicator = UIActivityIndicatorView(style: .large)
    private let statusLabel = UILabel()
    private let appGroupName = "group.com.familyplatform.app"
    private let defaultEndpoint = "https://familyplatform.com/api/media/native-share"

    override func viewDidLoad() {
        super.viewDidLoad()
        setupUI()
        processSharedMedia()
    }

    private func setupUI() {
        view.backgroundColor = UIColor(white: 0.1, alpha: 0.85)

        activityIndicator.color = .white
        activityIndicator.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(activityIndicator)
        activityIndicator.startAnimating()

        statusLabel.text = "Sharing to FamilyPlatform..."
        statusLabel.textColor = .white
        statusLabel.font = UIFont.systemFont(ofSize: 16, weight: .semibold)
        statusLabel.textAlignment = .center
        statusLabel.translatesAutoresizingMaskIntoConstraints = false
        view.addSubview(statusLabel)

        NSLayoutConstraint.activate([
            activityIndicator.centerXAnchor.constraint(equalTo: view.centerXAnchor),
            activityIndicator.centerYAnchor.constraint(equalTo: view.centerYAnchor, constant: -20),
            statusLabel.topAnchor.constraint(equalTo: activityIndicator.bottomAnchor, constant: 16),
            statusLabel.leadingAnchor.constraint(equalTo: view.leadingAnchor, constant: 20),
            statusLabel.trailingAnchor.constraint(equalTo: view.trailingAnchor, constant: -20)
        ])
    }

    private func processSharedMedia() {
        guard let extensionItems = extensionContext?.inputItems as? [NSExtensionItem] else {
            dismissExtension(withError: "No media items found")
            return
        }

        let sharedDefaults = UserDefaults(suiteName: appGroupName)
        let famCode = sharedDefaults?.string(forKey: "fam_code") ?? ""
        let userToken = sharedDefaults?.string(forKey: "user_token") ?? ""
        let apiEndpoint = sharedDefaults?.string(forKey: "api_endpoint") ?? defaultEndpoint

        var mediaItemsToUpload: [(Data, String, String)] = []
        let dispatchGroup = DispatchGroup()

        for item in extensionItems {
            guard let attachments = item.attachments else { continue }
            for provider in attachments {
                if provider.hasItemConformingToTypeIdentifier(UTType.image.identifier) {
                    dispatchGroup.enter()
                    provider.loadItem(forTypeIdentifier: UTType.image.identifier, options: nil) { (item, error) in
                        defer { dispatchGroup.leave() }
                        if let url = item as? URL, let data = try? Data(contentsOf: url) {
                            let filename = url.lastPathComponent
                            mediaItemsToUpload.append((data, filename, "image/jpeg"))
                        } else if let image = item as? UIImage, let data = image.jpegData(compressionQuality: 0.85) {
                            let filename = "family_\(UUID().uuidString).jpg"
                            mediaItemsToUpload.append((data, filename, "image/jpeg"))
                        }
                    }
                } else if provider.hasItemConformingToTypeIdentifier(UTType.movie.identifier) {
                    dispatchGroup.enter()
                    provider.loadItem(forTypeIdentifier: UTType.movie.identifier, options: nil) { (item, error) in
                        defer { dispatchGroup.leave() }
                        if let url = item as? URL, let data = try? Data(contentsOf: url) {
                            let filename = url.lastPathComponent
                            mediaItemsToUpload.append((data, filename, "video/mp4"))
                        }
                    }
                }
            }
        }

        dispatchGroup.notify(queue: .main) { [weak self] in
            guard let self = self else { return }
            if mediaItemsToUpload.isEmpty {
                self.dismissExtension(withError: "No valid images or videos could be processed.")
                return
            }
            self.uploadMedia(items: mediaItemsToUpload, endpoint: apiEndpoint, famCode: famCode, userToken: userToken)
        }
    }

    private func uploadMedia(items: [(Data, String, String)], endpoint: String, famCode: String, userToken: String) {
        guard let url = URL(string: endpoint) else {
            dismissExtension(withError: "Invalid upload configuration.")
            return
        }

        var request = URLRequest(url: url)
        request.httpMethod = "POST"
        let boundary = "Boundary-\(UUID().uuidString)"
        request.setValue("multipart/form-data; boundary=\(boundary)", forHTTPHeaderField: "Content-Type")
        if !famCode.isEmpty {
            request.setValue(famCode, forHTTPHeaderField: "X-Family-Code")
        }
        if !userToken.isEmpty {
            request.setValue(userToken, forHTTPHeaderField: "X-User-Token")
        }

        var body = Data()
        if !famCode.isEmpty {
            body.append("--\(boundary)\r\n".data(using: .utf8)!)
            body.append("Content-Disposition: form-data; name=\"fam_code\"\r\n\r\n".data(using: .utf8)!)
            body.append("\(famCode)\r\n".data(using: .utf8)!)
        }

        for (data, filename, mimeType) in items {
            body.append("--\(boundary)\r\n".data(using: .utf8)!)
            body.append("Content-Disposition: form-data; name=\"photo\"; filename=\"\(filename)\"\r\n".data(using: .utf8)!)
            body.append("Content-Type: \(mimeType)\r\n\r\n".data(using: .utf8)!)
            body.append(data)
            body.append("\r\n".data(using: .utf8)!)
        }
        body.append("--\(boundary)--\r\n".data(using: .utf8)!)

        let task = URLSession.shared.uploadTask(with: request, from: body) { [weak self] (data, response, error) in
            DispatchQueue.main.async {
                guard let self = self else { return }
                if let error = error {
                    self.dismissExtension(withError: error.localizedDescription)
                    return
                }

                if let httpResponse = response as? HTTPURLResponse, httpResponse.statusCode == 200 {
                    self.statusLabel.text = "Shared to Family Platform!"
                    DispatchQueue.main.asyncAfter(deadline: .now() + 1.0) {
                        self.extensionContext?.completeRequest(returningItems: [], completionHandler: nil)
                    }
                } else {
                    self.dismissExtension(withError: "Server returned error status.")
                }
            }
        }
        task.resume()
    }

    private func dismissExtension(withError message: String) {
        statusLabel.text = message
        activityIndicator.stopAnimating()
        DispatchQueue.main.asyncAfter(deadline: .now() + 2.0) { [weak self] in
            let error = NSError(domain: "com.familyplatform.share", code: -1, userInfo: [NSLocalizedDescriptionKey: message])
            self?.extensionContext?.cancelRequest(withError: error)
        }
    }
}
