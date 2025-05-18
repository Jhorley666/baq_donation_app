import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'dart:convert';
import 'package:crypto/crypto.dart';
import 'package:screenshot/screenshot.dart';
import 'package:share_plus/share_plus.dart';
import 'dart:io';
import 'package:path_provider/path_provider.dart';
import 'my_qr_gens_tab.dart';
import 'mentions_tab.dart';

class QRGeneratorScreen extends StatefulWidget {
  const QRGeneratorScreen({super.key});
  @override
  State<QRGeneratorScreen> createState() => _QRGeneratorScreenState();
}

class _QRGeneratorScreenState extends State<QRGeneratorScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _commissionController = TextEditingController();
  final _productNameController = TextEditingController(); // 1. Add controller
  final ScreenshotController _screenshotController = ScreenshotController();

  String? _qrData;
  String? _amountWarning;

  // Example static values for demonstration
  final String orderId = "123456";
  final String accountBanking = "9876543210";

  String _generateSha(String data) {
    return sha256.convert(utf8.encode(data)).toString();
  }

  void _generateQR() {
    setState(() {
      _amountWarning = null;
    });
    if (_formKey.currentState?.validate() ?? false) {
      final amount = double.tryParse(_amountController.text) ?? 0.0;
      if (amount <= 0) {
        setState(() {
          _amountWarning = "Amount must be greater than zero.";
        });
        return;
      }
      if (amount > 100.00) {
        setState(() {
          _amountWarning = "Amount must not exceed 100.00.";
        });
        return;
      }
      final commission = _commissionController.text;
      final productName = _productNameController.text; // 2. Get product name
      final data = jsonEncode({
        "order_id": orderId,
        "amount": amount.toStringAsFixed(2),
        "commission": commission,
        "product_type": productName, // 3. Use product name
        "account_banking": accountBanking,
      });
      final sha = _generateSha(data);
      final qrPayload = jsonEncode({
        "order_id": orderId,
        "amount": amount.toStringAsFixed(2),
        "commission": commission,
        "product_type": productName, // 3. Use product name
        "account_banking": accountBanking,
        "sha": sha,
      });
      setState(() {
        _qrData = qrPayload;
      });
    }
  }

  Future<void> _saveQrToGallery() async {
    if (_qrData == null) return;
    await Future.delayed(const Duration(milliseconds: 200)); // Wait for render
    final image = await _screenshotController.capture();
    if (image == null) return;
    final directory = await getApplicationDocumentsDirectory();
    final file = File('${directory.path}/qr_code.png');
    await file.writeAsBytes(image);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text('QR code saved to ${file.path}')),
    );
  }

  Future<void> _shareQr() async {
    if (_qrData == null) return;
    await Future.delayed(const Duration(milliseconds: 200)); // Wait for render
    final image = await _screenshotController.capture();
    if (image == null) return;
    final directory = await getTemporaryDirectory();
    final file = File('${directory.path}/qr_code.png');
    await file.writeAsBytes(image);
    await Share.shareXFiles([XFile(file.path)], text: 'Here is my QR code!');
  }

  @override
  Widget build(BuildContext context) {
    return DefaultTabController(
      length: 3,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('QR Generator'),
          bottom: const TabBar(
            tabs: [
              Tab(text: 'Generate QR', icon: Icon(Icons.qr_code)),
              Tab(text: 'My QRs', icon: Icon(Icons.history)),
              Tab(text: 'Mentions', icon: Icon(Icons.people)),
            ],
          ),
        ),
        body: TabBarView(
          children: [
            // Generate QR Tab
            Padding(
              padding: const EdgeInsets.all(16.0),
              child: Form(
                key: _formKey,
                child: ListView(
                  children: [
                    // 1. Product name label
                    const Text("Product name"),
                    // 2. Product name input text
                    TextFormField(
                      controller: _productNameController,
                      decoration: const InputDecoration(
                        hintText: "Enter product name",
                      ),
                      maxLength: 30,
                      validator: (value) {
                        if (value == null || value.trim().isEmpty) {
                          return "Product name is required";
                        }
                        if (value.trim().length < 3) {
                          return "Minimum 3 characters";
                        }
                        if (value.trim().length > 30) {
                          return "Maximum 30 characters";
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 16),
                    const Text("Enter the price of the product"),
                    TextFormField(
                      controller: _amountController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(
                        hintText: "e.g. 12.34",
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) return "Enter a value";
                        final doubleValue = double.tryParse(value);
                        if (doubleValue == null) return "Enter a valid number";
                        if (!RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(value)) {
                          return "Format must be #.##";
                        }
                        return null;
                      },
                    ),
                    if (_amountWarning != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 4.0),
                        child: Text(
                          _amountWarning!,
                          style: const TextStyle(color: Colors.red),
                        ),
                      ),
                    const SizedBox(height: 16),
                    const Text("Enter comission for donation"),
                    TextFormField(
                      controller: _commissionController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      decoration: const InputDecoration(
                        hintText: "e.g. 1.23",
                      ),
                      validator: (value) {
                        if (value == null || value.isEmpty) return "Enter a value";
                        final doubleValue = double.tryParse(value);
                        if (doubleValue == null) return "Enter a valid number";
                        if (!RegExp(r'^\d+(\.\d{1,2})?$').hasMatch(value)) {
                          return "Format must be #.##";
                        }
                        return null;
                      },
                    ),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: _generateQR,
                      child: const Text("Generate QR"),
                    ),
                    const SizedBox(height: 24),
                    if (_qrData != null)
                      Column(
                        children: [
                          const Text("Generated QR:"),
                          Screenshot(
                            controller: _screenshotController,
                            child: Container(
                              color: Colors.white,
                              padding: const EdgeInsets.all(8.0),
                              child: QrImageView(
                                data: _qrData!,
                                version: QrVersions.auto,
                                size: 200.0,
                                backgroundColor: Colors.white,
                                foregroundColor: Colors.black,
                              ),
                            ),
                          ),
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              ElevatedButton.icon(
                                onPressed: _saveQrToGallery,
                                icon: const Icon(Icons.save),
                                label: const Text("Save"),
                              ),
                              const SizedBox(width: 16),
                              ElevatedButton.icon(
                                onPressed: _shareQr,
                                icon: const Icon(Icons.share),
                                label: const Text("Share"),
                              ),
                            ],
                          ),
                        ],
                      ),
                  ],
                ),
              ),
            ),
            // My QRs Tab
            const MyQrGensTab(),
            // Mentions Tab
            const MentionsTab(),
          ],
        ),
      ),
    );
  }
}