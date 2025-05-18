import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';
import 'dart:convert';
import 'package:crypto/crypto.dart';
import 'package:screenshot/screenshot.dart';
import 'package:share_plus/share_plus.dart';
import 'dart:io';
import 'package:path_provider/path_provider.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'QR Generator',
      home: QRGeneratorScreen(),
    );
  }
}

class QRGeneratorScreen extends StatefulWidget {
  const QRGeneratorScreen({super.key});
  @override
  State<QRGeneratorScreen> createState() => _QRGeneratorScreenState();
}

class _QRGeneratorScreenState extends State<QRGeneratorScreen> {
  final _formKey = GlobalKey<FormState>();
  final _amountController = TextEditingController();
  final _commissionController = TextEditingController();
  final ScreenshotController _screenshotController = ScreenshotController();

  String? _qrData;
  String? _amountWarning;

  // Example static values for demonstration
  final String orderId = "123456";
  final String productType = "ExampleProduct";
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
      final data = jsonEncode({
        "order_id": orderId,
        "amount": amount.toStringAsFixed(2),
        "commission": commission,
        "product_type": productType,
        "account_banking": accountBanking,
      });
      final sha = _generateSha(data);
      final qrPayload = jsonEncode({
        "order_id": orderId,
        "amount": amount.toStringAsFixed(2),
        "commission": commission,
        "product_type": productType,
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
    final image = await _screenshotController.capture();
    if (image == null) return;
    final directory = await getTemporaryDirectory();
    final file = File('${directory.path}/qr_code.png');
    await file.writeAsBytes(image);
    await Share.shareXFiles([XFile(file.path)], text: 'Here is my QR code!');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('QR Generator')),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
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
                      child: QrImageView(
                        data: _qrData!,
                        version: QrVersions.auto,
                        size: 200.0,
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
    );
  }
}