import { ref, computed, readonly } from 'vue'

/**
 * Composable para gerenciar localizações de Moçambique
 * Fornece províncias, distritos e bairros dinamicamente
 */
export function useMozambiqueLocations() {
  // Dados de províncias, distritos e bairros de Moçambique
  const locations = ref({
    'Maputo': {
      'KaMpfumu': ['Centro', 'Malhangalene', 'Polana Caniço', 'Sommerschield'],
      'Nlhamankulu': ['Aeroporto', 'Central', 'Malanga', 'Munhuana'],
      'KaMaxaquene': ['Alto Maé', 'Costa do Sol', 'Fomento', 'Polana'],
      'KaMavota': ['Bairro Central', 'Chamanculo', 'Malanga', 'Munhuana'],
      'KaMubukwana': ['Bairro Central', 'Costa do Sol', 'Jardim', 'Sommerchield'],
      'KaTembe': ['Belém', 'Chabeco', 'Incassane', 'Tembe'],
      'Kanyaka': ['Bairro Central', 'Malhangalene', 'Polana Caniço']
    },
    'Maputo Provincia': {
      'Boane': ['Boane-Sede', 'Eduardo Mondlane', 'Matola-Rio'],
      'Magude': ['Magude-Sede', 'Mahele', 'Mapulanguene', 'Motaze'],
      'Manhiça': ['3 de Fevereiro', 'Calanga', 'Ilha Josina Machel', 'Maluana', 'Manhiça-Sede', 'Xinavane'],
      'Marracuene': ['Marracuene-Sede', 'Machubo', 'Ndlavela'],
      'Matutuíne': ['Bela Vista', 'Catembe', 'Catembe Nova', 'Matutuíne-Sede'],
      'Moamba': ['Moamba-Sede', 'Pessene', 'Ressano Garcia', 'Sabie'],
      'Namaacha': ['Namaacha-Sede', 'Changalane', 'Macaringue']
    },
    'Gaza': {
      'Chókwè': ['Chókwè-Sede', 'Lionde', 'Macarretane', 'Xilembene'],
      'Chibuto': ['Chibuto-Sede', 'Alto Changane', 'Chaimite', 'Godide', 'Malei'],
      'Xai-Xai': ['Xai-Xai-Sede', 'Chongoene', 'Praia do Bilene', 'Zavala'],
      'Manjacaze': ['Manjacaze-Sede', 'Calanga', 'Macuacua', 'Mazivila'],
      'Massingir': ['Massingir-Sede', 'Mavodze', 'Zulo']
    },
    'Inhambane': {
      'Inhambane': ['Inhambane-Sede', 'Barragem', 'Chamite', 'Gingó', 'Jofane'],
      'Maxixe': ['Maxixe-Sede', 'Chaimite', 'Mabil', 'Quissico'],
      'Vilankulo': ['Vilankulo-Sede', 'Munguara', 'Závora']
    },
    'Sofala': {
      'Beira': ['Beira-Sede', 'Chaimite', 'Macuti', 'Munhava', 'Nhacutse'],
      'Dondo': ['Dondo-Sede', 'Savane'],
      'Nhamatanda': ['Nhamatanda-Sede', 'Muzamba'],
      'Búzi': ['Búzi-Sede', 'Estaquinha', 'Sofala'],
      'Gorongosa': ['Gorongosa-Sede', 'Vanduzi']
    },
    'Manica': {
      'Chimoio': ['Chimoio-Sede', 'Chemba', 'Mopeia', 'Vanduzi'],
      'Gondola': ['Gondola-Sede', 'Canda', 'Matsinho'],
      'Manica': ['Manica-Sede', 'Barué', 'Guro', 'Machaze'],
      'Báruè': ['Báruè-Sede', 'Chipera', 'Goonda'],
      'Sussundenga': ['Sussundenga-Sede', 'Dombe', 'Muoha']
    },
    'Tete': {
      'Tete': ['Tete-Sede', 'Changara', 'Chipera', 'Mphende', 'Mukumbura'],
      'Moatize': ['Moatize-Sede', 'Kambulatsitsi', 'Zóbue'],
      'Angónia': ['Angónia-Sede', 'Dete', 'Ulongué'],
      'Cahora-Bassa': ['Cahora-Bassa-Sede', 'Chitima', 'Mpue'],
      'Changara': ['Changara-Sede', 'Kachembe', 'Kavumba']
    },
    'Zambezia': {
      'Quelimane': ['Quelimane-Sede', 'Mocuba', 'Pebane'],
      'Alto Molócuè': ['Alto Molócuè-Sede', 'Nauela'],
      'Chinde': ['Chinde-Sede', 'Bajone'],
      'Gile': ['Gile-Sede', 'Alto Ligonha'],
      'Gurué': ['Gurué-Sede', 'Ile', 'Maganja']
    },
    'Nampula': {
      'Nampula': ['Nampula-Sede', 'Ilha de Moçambique', 'Mecubúri'],
      'Angoche': ['Angoche-Sede', 'Aube', 'Namaponda'],
      'Nacala Porto': ['Nacala Porto-Sede', 'Ilha de Goa', 'Mutiva'],
      'Monapo': ['Monapo-Sede', 'Itoculo', 'Netia']
    },
    'Cabo Delgado': {
      'Pemba': ['Pemba-Sede', 'Metuge', 'Quissanga'],
      'Mocímboa da Praia': ['Mocímboa da Praia-Sede', 'Diaca', 'Mbau'],
      'Montepuez': ['Montepuez-Sede', 'Mavago', 'Namuno'],
      'Mueda': ['Mueda-Sede', 'Chapa', 'Negomano']
    },
    'Niassa': {
      'Lichinga': ['Lichinga-Sede', 'Meponda', 'Mulumba'],
      'Cuamba': ['Cuamba-Sede', 'Lurio', 'Maúa'],
      'Mandimba': ['Mandimba-Sede', 'Massaú', 'Mitande'],
      'Marrupa': ['Marrupa-Sede', 'Marangira', 'Nungo']
    }
  })

  // Províncias disponíveis
  const provinces = computed(() => Object.keys(locations.value).sort())

  // Função para obter distritos de uma província
  const getDistrictsForProvince = (province) => {
    if (!province || !locations.value[province]) return []
    return Object.keys(locations.value[province])
  }

  // Função para obter bairros de um distrito
  const getNeighborhoodsForDistrict = (province, district) => {
    if (!province || !district || !locations.value[province] || !locations.value[province][district]) {
      return []
    }
    return locations.value[province][district] || []
  }

  // Computed para distritos baseado na província selecionada
  const districtsForSelectedProvince = (selectedProvince) => {
    return computed(() => {
      const districts = getDistrictsForProvince(selectedProvince)
      return districts
    })
  }

  // Computed para bairros baseado no distrito selecionado
  const neighborhoodsForSelectedDistrict = (selectedProvince, selectedDistrict) => {
    return computed(() => {
      const neighborhoods = getNeighborhoodsForDistrict(selectedProvince, selectedDistrict)
      return neighborhoods
    })
  }

  // Validação se distrito pertence à província
  const isValidDistrictForProvince = (province, district) => {
    if (!province || !district) return false
    const districts = getDistrictsForProvince(province)
    return districts.includes(district)
  }

  // Buscar província por distrito
  const getProvinceForDistrict = (district) => {
    for (const [province, districts] of Object.entries(locations.value)) {
      if (Array.isArray(districts)) {
        if (districts.includes(district)) {
          return province
        }
      } else {
        if (Object.keys(districts).includes(district)) {
          return province
        }
      }
    }
    return null
  }

  return {
    locations: readonly(locations),
    provinces,
    getDistrictsForProvince,
    getNeighborhoodsForDistrict,
    districtsForSelectedProvince,
    neighborhoodsForSelectedDistrict,
    isValidDistrictForProvince,
    getProvinceForDistrict
  }
}
