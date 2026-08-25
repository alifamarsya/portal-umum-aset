// SPDX-License-Identifier: MIT
pragma solidity ^0.8.20;

// Contract sangat sederhana: cuma "kotak penyimpanan" hash gabungan
// audit log Portum, supaya bisa dibuktikan tersimpan di blockchain publik
// dan tidak bisa diubah diam-diam.
contract AuditAnchor {

    // Struktur satu "anchor" (satu titik penyimpanan hash)
    struct Anchor {
        bytes32 rootHash;   // hash gabungan audit log periode tsb
        uint256 timestamp;  // waktu disimpan (otomatis dari blockchain)
        address pengirim;   // wallet yang mengirim (identitas Portum)
    }

    // Daftar semua anchor yang pernah dikirim, bisa dibaca siapa saja
    Anchor[] public anchors;

    // "Pengumuman" tiap kali ada anchor baru — supaya mudah dilacak di Etherscan
    event AnchorTersimpan(uint256 index, bytes32 rootHash, uint256 timestamp);

    // Fungsi utama: simpan satu root hash baru ke blockchain
    function anchorHash(bytes32 _rootHash) public {
        anchors.push(Anchor({
            rootHash: _rootHash,
            timestamp: block.timestamp,
            pengirim: msg.sender
        }));
        emit AnchorTersimpan(anchors.length - 1, _rootHash, block.timestamp);
    }

    // Fungsi bantu: berapa banyak anchor yang sudah tersimpan
    function totalAnchor() public view returns (uint256) {
        return anchors.length;
    }
}