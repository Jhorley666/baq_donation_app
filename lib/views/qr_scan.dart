import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

class QRScanScreen extends StatefulWidget {
  const QRScanScreen({super.key});

  @override
  State<QRScanScreen> createState() => _QRScanScreenState();
}

class _QRScanScreenState extends State<QRScanScreen> {
  String? scannedData;
  MobileScannerController scannerController = MobileScannerController();

  void _onDetect(BarcodeCapture capture) {
    final barcode = capture.barcodes.first;
    if (barcode.rawValue != null && scannedData == null) {
      setState(() {
        scannedData = barcode.rawValue;
      });
      scannerController.stop();
    }
  }

  @override
  void dispose() {
    scannerController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan QR Code')),
      body: Column(
        children: [
          Expanded(
            flex: 4,
            child: Stack(
              children: [
                MobileScanner(
                  controller: scannerController,
                  onDetect: _onDetect,
                  overlay: Container(
                    decoration: ShapeDecoration(
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                        side: const BorderSide(
                          color: Colors.yellow,
                          width: 4,
                        ),
                      ),
                    ),
                    margin: const EdgeInsets.all(40),
                  ),
                ),
                if (scannedData != null)
                  Container(
                    color: Colors.black54,
                    child: const Center(
                      child: CircularProgressIndicator(),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            flex: 1,
            child: Center(
              child: scannedData != null
                  ? Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text('Scanned Data:', style: Theme.of(context).textTheme.titleMedium),
                        const SizedBox(height: 8),
                        Text(scannedData!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: () {
                            setState(() {
                              scannedData = null;
                            });
                            scannerController.start();
                          },
                          child: const Text('Scan Again'),
                        ),
                      ],
                    )
                  : const Text('Point your camera at a QR code'),
            ),
          ),
        ],
      ),
    );
  }
}