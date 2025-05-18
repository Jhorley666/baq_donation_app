import 'package:flutter/material.dart';
import 'views/home.dart';
import 'views/qr_gen.dart';
import 'views/donation.dart';
import 'views/qr_scan.dart';
import 'views/register.dart';

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'BAQ Donation App',
      home: const HomePage(),
      routes: {
        '/qr-generator': (context) => const QRGeneratorScreen(),
        '/donation': (context) => const DonationScreen(),
        '/qr-scan': (context) => const QRScanScreen(),
        '/register': (context) => const RegisterScreen(),
      },
    );
  }
}